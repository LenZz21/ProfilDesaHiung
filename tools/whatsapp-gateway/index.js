const path = require("path");
require("dotenv").config({ path: path.join(__dirname, ".env") });

const fs = require("fs");
const express = require("express");
const cors = require("cors");
const QRCode = require("qrcode");
const { Client, LocalAuth, MessageMedia } = require("whatsapp-web.js");

const app = express();

const PORT = Number(process.env.PORT || 3070);
const API_TOKEN = (process.env.API_TOKEN || "").trim();
const CHROME_EXECUTABLE_PATH = (process.env.CHROME_EXECUTABLE_PATH || "").trim();
const WA_CLIENT_ID = (process.env.WA_CLIENT_ID || "profil-desa-minimal-v2").trim();
const DEFAULT_AUTO_REPLY_ENABLED = String(process.env.WA_AUTO_REPLY_ENABLED || "true").toLowerCase() === "true";
const DEFAULT_AUTO_REPLY_TEXT = String(
    process.env.WA_AUTO_REPLY_TEXT ||
        "Halo, pesan Anda sudah masuk ke Admin Kampung. Mohon tunggu, kami akan membalas secepatnya."
).trim();
const DEFAULT_AUTO_REPLY_COOLDOWN_SECONDS = Math.max(0, Number(process.env.WA_AUTO_REPLY_COOLDOWN_SECONDS || 0));
const DEFAULT_AUTO_REPLY_IGNORE_NUMBERS_RAW = String(process.env.WA_AUTO_REPLY_IGNORE_NUMBERS || "");
const DEFAULT_MARK_AS_SEEN_ENABLED = String(process.env.WA_MARK_AS_SEEN_ENABLED || "true").toLowerCase() === "true";
const RUNTIME_SETTINGS_PATH = (process.env.WA_RUNTIME_SETTINGS_PATH || path.join(__dirname, "runtime-settings.json"))
    .trim();
const WA_SESSION_DATA_PATH = (process.env.WA_SESSION_DATA_PATH || path.join(__dirname, ".wwebjs_auth")).trim();
const HEALTHCHECK_INTERVAL_MS = Math.max(10000, Number(process.env.WA_HEALTHCHECK_INTERVAL_MS || 30000));
const UNHEALTHY_RECONNECT_AFTER_MS = Math.max(30000, Number(process.env.WA_UNHEALTHY_RECONNECT_AFTER_MS || 120000));
const SERVER_BIND_RETRY_MS = Math.max(2000, Number(process.env.WA_SERVER_BIND_RETRY_MS || 5000));

let isClientReady = false;
let latestQrDataUrl = "";
let status = "initializing";
let reconnectTimer = null;
let reconnectAttempt = 0;
let isReconnecting = false;
let unhealthySince = 0;
let server = null;
let serverRetryTimer = null;
let healthcheckTimer = null;
const autoReplyLastSentAt = new Map();
let runtimeSettingsCache = {
    loadedAt: 0,
    data: null,
};
const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const clearReconnectTimer = () => {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }
};

const clearServerRetryTimer = () => {
    if (serverRetryTimer) {
        clearTimeout(serverRetryTimer);
        serverRetryTimer = null;
    }
};

const markClientHealthy = () => {
    unhealthySince = 0;
};

const markClientUnhealthy = () => {
    if (!unhealthySince) {
        unhealthySince = Date.now();
    }
};

const scheduleReconnect = (reason = "unknown") => {
    if (isReconnecting) {
        return;
    }

    clearReconnectTimer();
    reconnectAttempt += 1;
    const delay = Math.min(30000, 2000 * reconnectAttempt);
    status = "reconnecting";
    isClientReady = false;
    markClientUnhealthy();
    latestQrDataUrl = "";

    reconnectTimer = setTimeout(async () => {
        isReconnecting = true;
        try {
            console.warn(`[wa] reconnect attempt #${reconnectAttempt} (${reason})`);
            try {
                await client.destroy();
            } catch {
                // Ignore destroy errors; client may already be closed.
            }
            await sleep(700);
            await client.initialize();
        } catch (error) {
            console.error("[wa] reconnect gagal:", error?.message || error);
            isReconnecting = false;
            scheduleReconnect("retry-after-failed-reconnect");
            return;
        }

        isReconnecting = false;
    }, delay);
};

const isTransientSendError = (errorMessage) => {
    const message = String(errorMessage || "");
    return /detached Frame|Execution context was destroyed|Target closed|Session closed|Protocol error|Navigation failed/i.test(
        message
    );
};

const cropWhiteQrMargin = (pngBuffer) => {
    const { PNG } = require("pngjs");
    const image = PNG.sync.read(pngBuffer);
    const { width, height, data } = image;

    let minX = width;
    let minY = height;
    let maxX = -1;
    let maxY = -1;

    for (let y = 0; y < height; y += 1) {
        for (let x = 0; x < width; x += 1) {
            const i = (width * y + x) * 4;
            const r = data[i];
            const g = data[i + 1];
            const b = data[i + 2];
            const a = data[i + 3];

            const isWhite = r > 245 && g > 245 && b > 245 && a > 245;
            if (!isWhite) {
                if (x < minX) minX = x;
                if (y < minY) minY = y;
                if (x > maxX) maxX = x;
                if (y > maxY) maxY = y;
            }
        }
    }

    if (maxX < 0 || maxY < 0) {
        return pngBuffer;
    }

    const croppedWidth = maxX - minX + 1;
    const croppedHeight = maxY - minY + 1;
    const cropped = new PNG({ width: croppedWidth, height: croppedHeight });

    PNG.bitblt(image, cropped, minX, minY, croppedWidth, croppedHeight, 0, 0);
    return PNG.sync.write(cropped);
};

const normalizePhoneNumber = (input) => {
    let value = String(input || "").replace(/\D+/g, "");
    if (!value) return "";

    if (value.startsWith("0")) {
        value = "62" + value.slice(1);
    } else if (value.startsWith("8")) {
        value = "62" + value;
    }

    return value;
};

const toWhatsAppId = (input) => {
    const number = normalizePhoneNumber(input);
    if (!number) return "";
    return `${number}@c.us`;
};

const toCanonicalSenderKey = (input) => {
    const value = String(input || "").trim().toLowerCase();
    if (!value) return "";
    return value.replace(/@(c\.us|lid)$/i, "").replace(/@.+$/, "");
};

const parseBoolean = (value, fallback) => {
    if (typeof value === "boolean") return value;
    const normalized = String(value ?? "").trim().toLowerCase();
    if (["1", "true", "yes", "on"].includes(normalized)) return true;
    if (["0", "false", "no", "off"].includes(normalized)) return false;
    return fallback;
};

const parseIgnoreNumbers = (rawValue) => {
    const raw =
        Array.isArray(rawValue)
            ? rawValue.join(",")
            : String(rawValue ?? "");

    return Array.from(new Set(raw
        .split(",")
        .map((v) => v.trim())
        .filter(Boolean)
        .flatMap((v) => {
            const canonical = toCanonicalSenderKey(v);
            if (!canonical) return [];
            return [`${canonical}@c.us`, `${canonical}@lid`];
        })
        .filter(Boolean)));
};

const markIncomingAsSeen = async (chatId) => {
    if (!chatId || typeof client.sendSeen !== "function") {
        return false;
    }

    const senderKey = toCanonicalSenderKey(chatId);
    const candidates = [String(chatId).trim()];
    if (senderKey) {
        candidates.push(`${senderKey}@lid`, `${senderKey}@c.us`);
    }

    const uniqueCandidates = Array.from(new Set(candidates.filter(Boolean)));
    for (let attempt = 1; attempt <= 3; attempt += 1) {
        for (const candidate of uniqueCandidates) {
            try {
                await client.sendSeen(candidate);
                return true;
            } catch {
                // Try next candidate format.
            }
        }

        if (attempt < 3) {
            await sleep(450 * attempt);
        }
    }

    return false;
};

const loadRuntimeSettings = () => {
    const now = Date.now();
    if (runtimeSettingsCache.data && now - runtimeSettingsCache.loadedAt < 3000) {
        return runtimeSettingsCache.data;
    }

    let fileSettings = {};
    if (RUNTIME_SETTINGS_PATH && fs.existsSync(RUNTIME_SETTINGS_PATH)) {
        try {
            const raw = fs.readFileSync(RUNTIME_SETTINGS_PATH, "utf8");
            // Tolerate UTF-8 BOM so runtime settings don't silently fall back to .env defaults.
            const sanitized = String(raw || "").replace(/^\uFEFF/, "");
            fileSettings = JSON.parse(sanitized);
        } catch (error) {
            console.warn("[wa] runtime settings tidak valid, pakai default:", error?.message || error);
            fileSettings = {};
        }
    }

    const runtime = {
        enabled: parseBoolean(fileSettings.auto_reply_enabled, DEFAULT_AUTO_REPLY_ENABLED),
        text: String(fileSettings.auto_reply_text ?? DEFAULT_AUTO_REPLY_TEXT).trim(),
        cooldownSeconds: Math.max(
            0,
            Number(fileSettings.auto_reply_cooldown_seconds ?? DEFAULT_AUTO_REPLY_COOLDOWN_SECONDS)
        ),
        ignoreNumbers: parseIgnoreNumbers(
            fileSettings.auto_reply_ignore_numbers ?? DEFAULT_AUTO_REPLY_IGNORE_NUMBERS_RAW
        ),
        markAsSeenEnabled: parseBoolean(fileSettings.mark_as_seen_enabled, DEFAULT_MARK_AS_SEEN_ENABLED),
    };

    runtimeSettingsCache = {
        loadedAt: now,
        data: runtime,
    };

    return runtime;
};

const isAuthorized = (req) => {
    if (!API_TOKEN) return true;
    return String(req.headers.authorization || "") === `Bearer ${API_TOKEN}`;
};

const puppeteerOptions = {
    headless: true,
    args: [
        "--no-sandbox",
        "--disable-setuid-sandbox",
        "--disable-dev-shm-usage",
        "--disable-accelerated-2d-canvas",
        "--no-first-run",
        "--no-zygote",
        "--disable-gpu",
    ],
};

if (CHROME_EXECUTABLE_PATH) {
    puppeteerOptions.executablePath = CHROME_EXECUTABLE_PATH;
}

const client = new Client({
    authStrategy: new LocalAuth({
        clientId: WA_CLIENT_ID || "profil-desa-minimal-v2",
        dataPath: WA_SESSION_DATA_PATH || path.join(__dirname, ".wwebjs_auth"),
    }),
    restartOnAuthFail: true,
    puppeteer: puppeteerOptions,
});

app.use(cors());
app.use(express.json({ limit: "1mb" }));

const startHttpServer = () => {
    if (server) {
        return;
    }

    const nextServer = app.listen(PORT, () => {
        clearServerRetryTimer();
        server = nextServer;
        const autoReply = loadRuntimeSettings();
        console.log(`[http] gateway minimal aktif di http://127.0.0.1:${PORT}`);
        console.log(`[wa] cwd: ${process.cwd()}`);
        console.log(`[wa] runtime settings file: ${RUNTIME_SETTINGS_PATH}`);
        console.log(`[wa] session data path: ${WA_SESSION_DATA_PATH}`);
        console.log(
            `[wa] auto-reply ${autoReply.enabled ? "aktif" : "nonaktif"} (cooldown ${autoReply.cooldownSeconds}s)`
        );
    });

    nextServer.on("error", (error) => {
        server = null;
        const errorCode = String(error?.code || "").toUpperCase();

        if (errorCode === "EADDRINUSE") {
            console.error(
                `[http] port ${PORT} sedang dipakai proses lain. Retry bind dalam ${SERVER_BIND_RETRY_MS / 1000}s...`
            );
            clearServerRetryTimer();
            serverRetryTimer = setTimeout(() => {
                startHttpServer();
            }, SERVER_BIND_RETRY_MS);
            return;
        }

        console.error("[http] server error:", error?.message || error);
        clearServerRetryTimer();
        serverRetryTimer = setTimeout(() => {
            startHttpServer();
        }, SERVER_BIND_RETRY_MS);
    });
};

const startHealthcheckLoop = () => {
    if (healthcheckTimer) {
        clearInterval(healthcheckTimer);
    }

    healthcheckTimer = setInterval(async () => {
        if (isReconnecting) {
            return;
        }

        let waState = "UNKNOWN";
        try {
            waState = String(await client.getState()).toUpperCase();
        } catch (error) {
            waState = "UNKNOWN";
            console.warn("[wa] healthcheck gagal baca state:", error?.message || error);
        }

        if (waState === "CONNECTED") {
            if (!isClientReady) {
                console.warn("[wa] healthcheck: state CONNECTED, sync ready=true");
            }

            isClientReady = true;
            status = "ready";
            reconnectAttempt = 0;
            markClientHealthy();
            return;
        }

        isClientReady = false;
        if (waState && waState !== "UNKNOWN") {
            status = waState.toLowerCase();
        }
        markClientUnhealthy();

        const unhealthyDuration = Date.now() - unhealthySince;
        if (unhealthyDuration >= UNHEALTHY_RECONNECT_AFTER_MS) {
            console.warn(
                `[wa] healthcheck: state ${waState}, tidak sehat > ${UNHEALTHY_RECONNECT_AFTER_MS / 1000}s, reconnect...`
            );
            scheduleReconnect(`healthcheck:${waState.toLowerCase()}`);
            unhealthySince = Date.now();
        }
    }, HEALTHCHECK_INTERVAL_MS);
};

app.get("/", (_req, res) => {
    res.json({
        ok: true,
        service: "whatsapp-gateway-minimal",
        status,
        ready: isClientReady,
    });
});

app.get("/api/v1/status", (_req, res) => {
    res.json({
        ok: true,
        status,
        ready: isClientReady,
        qr_available: Boolean(latestQrDataUrl),
    });
});

app.get("/api/v1/qr", (_req, res) => {
    if (!latestQrDataUrl) {
        return res.status(404).json({
            ok: false,
            message: isClientReady
                ? "WhatsApp sudah terhubung. QR tidak diperlukan."
                : "QR belum tersedia. Tunggu beberapa detik lalu refresh.",
        });
    }

    return res.json({
        ok: true,
        qr: latestQrDataUrl,
    });
});

app.get("/api/v1/qr-image", (_req, res) => {
    if (!latestQrDataUrl) {
        return res.status(404).json({
            ok: false,
            message: isClientReady
                ? "WhatsApp sudah terhubung. QR tidak diperlukan."
                : "QR belum tersedia. Tunggu beberapa detik lalu refresh.",
        });
    }

    const base64 = latestQrDataUrl.replace(/^data:image\/png;base64,/, "");
    const rawBuffer = Buffer.from(base64, "base64");
    let buffer = rawBuffer;

    try {
        buffer = cropWhiteQrMargin(rawBuffer);
    } catch (error) {
        console.warn("[wa] crop qr margin gagal, pakai gambar asli:", error?.message || error);
    }

    res.setHeader("Content-Type", "image/png");
    res.setHeader("Cache-Control", "no-store, no-cache, must-revalidate, max-age=0");
    return res.send(buffer);
});

app.get("/api/v1/qr-view", (_req, res) => {
    if (!latestQrDataUrl) {
        const note = isClientReady
            ? "WhatsApp sudah terhubung. QR tidak diperlukan."
            : "QR belum tersedia. Tunggu beberapa detik lalu refresh.";

        return res.status(404).send(
            `<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    html, body {
      margin: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      font-family: Arial, sans-serif;
      background: #f7f7f7;
      color: #1f2937;
    }
    .wrap {
      width: 100%;
      height: 100%;
      display: grid;
      place-items: center;
      padding: 16px;
      box-sizing: border-box;
      text-align: center;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="wrap"><strong>${note}</strong></div>
</body>
</html>`
        );
    }

    return res.send(`<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>QR Login WhatsApp</title>
  <style>
    html, body {
      margin: 0;
      width: 100%;
      height: 100%;
      overflow: hidden;
      font-family: Arial, sans-serif;
      background: #f7f7f7;
      color: #111827;
    }
    .wrap {
      width: 100%;
      height: 100%;
      display: grid;
      place-items: center;
      padding: 14px;
      box-sizing: border-box;
    }
    .card {
      width: 100%;
      max-width: 360px;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 14px;
      box-sizing: border-box;
      text-align: center;
    }
    h3 {
      margin: 0 0 10px;
      font-size: 20px;
    }
    img {
      width: 100%;
      height: auto;
      border: 1px solid #eee;
      border-radius: 8px;
    }
    p {
      margin: 10px 0 0;
      font-size: 13px;
      color: #4b5563;
      line-height: 1.5;
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <h3>Scan QR Login WhatsApp</h3>
      <img src="${latestQrDataUrl}" alt="QR Login" />
      <p>Buka WhatsApp di HP → Perangkat Tertaut → Tautkan Perangkat.</p>
    </div>
  </div>
</body>
</html>`);
});

app.post("/api/v1/send-message", async (req, res) => {
    if (!isAuthorized(req)) {
        return res.status(401).json({ ok: false, message: "Unauthorized token." });
    }

    const rawNumber = normalizePhoneNumber(req.body.to || req.body.phone || req.body.number);
    const to = toWhatsAppId(rawNumber);
    const message = String(req.body.message || req.body.text || "").trim();

    if (!to || !message) {
        return res.status(422).json({
            ok: false,
            message: "Field to dan message wajib diisi.",
        });
    }

    if (!isClientReady) {
        return res.status(503).json({
            ok: false,
            message: "Client belum ready. Scan QR dulu.",
        });
    }

    try {
        let waState = "UNKNOWN";
        try {
            waState = String(await client.getState()).toUpperCase();
        } catch {
            waState = "UNKNOWN";
        }

        if (waState !== "CONNECTED") {
            status = waState.toLowerCase();
            isClientReady = false;

            return res.status(503).json({
                ok: false,
                message: `Client belum stabil (${waState}). Coba lagi beberapa detik.`,
            });
        }

        let targetId = to;
        try {
            const numberId = await client.getNumberId(rawNumber);
            if (!numberId?._serialized) {
                return res.status(422).json({
                    ok: false,
                    message: "Nomor tujuan belum terdaftar di WhatsApp atau format nomor tidak valid.",
                });
            }

            // Pakai ID kanonik dari WhatsApp untuk mencegah chat dobel pada nomor yang sama.
            targetId = String(numberId._serialized || to);
        } catch {
            // Fallback ke ID berbasis nomor jika resolver tidak tersedia.
            targetId = to;
        }

        let sent = null;
        let lastError = null;

        for (let attempt = 1; attempt <= 3; attempt += 1) {
            try {
                sent = await client.sendMessage(targetId, message);
                lastError = null;
                break;
            } catch (error) {
                lastError = error;
                if (attempt < 3 && isTransientSendError(error?.message)) {
                    await sleep(800 * attempt);
                    continue;
                }
                break;
            }
        }

        if (!sent && lastError) {
            const errMessage = String(lastError?.message || "Gagal kirim pesan.");
            if (isTransientSendError(errMessage)) {
                return res.status(503).json({
                    ok: false,
                    message: "Sesi WhatsApp sedang transisi. Coba kirim ulang beberapa detik lagi.",
                });
            }

            return res.status(500).json({
                ok: false,
                message: errMessage,
            });
        }

        return res.json({
            ok: true,
            to: targetId,
            id: sent?.id?._serialized || null,
        });
    } catch (error) {
        return res.status(500).json({
            ok: false,
            message: error?.message || "Gagal kirim pesan.",
        });
    }
});

app.post("/api/v1/send-file", async (req, res) => {
    if (!isAuthorized(req)) {
        return res.status(401).json({ ok: false, message: "Unauthorized token." });
    }

    const rawNumber = normalizePhoneNumber(req.body.to || req.body.phone || req.body.number);
    const to = toWhatsAppId(rawNumber);
    const filePath = String(req.body.path || "").trim();
    const caption = String(req.body.caption || "").trim();

    if (!to || !filePath) {
        return res.status(422).json({
            ok: false,
            message: "Field to dan path wajib diisi.",
        });
    }

    if (!isClientReady) {
        return res.status(503).json({
            ok: false,
            message: "Client belum ready. Scan QR dulu.",
        });
    }

    if (!fs.existsSync(filePath)) {
        return res.status(422).json({
            ok: false,
            message: "File tidak ditemukan di server gateway.",
        });
    }

    try {
        let waState = "UNKNOWN";
        try {
            waState = String(await client.getState()).toUpperCase();
        } catch {
            waState = "UNKNOWN";
        }

        if (waState !== "CONNECTED") {
            status = waState.toLowerCase();
            isClientReady = false;

            return res.status(503).json({
                ok: false,
                message: `Client belum stabil (${waState}). Coba lagi beberapa detik.`,
            });
        }

        let targetId = to;
        try {
            const numberId = await client.getNumberId(rawNumber);
            if (!numberId?._serialized) {
                return res.status(422).json({
                    ok: false,
                    message: "Nomor tujuan belum terdaftar di WhatsApp atau format nomor tidak valid.",
                });
            }

            // Pakai ID kanonik dari WhatsApp untuk mencegah chat dobel pada nomor yang sama.
            targetId = String(numberId._serialized || to);
        } catch {
            // Fallback ke ID berbasis nomor jika resolver tidak tersedia.
            targetId = to;
        }

        const media = MessageMedia.fromFilePath(filePath);
        const sendOptions = caption ? { caption } : {};
        let sent = null;
        let lastError = null;

        for (let attempt = 1; attempt <= 3; attempt += 1) {
            try {
                sent = await client.sendMessage(targetId, media, sendOptions);
                lastError = null;
                break;
            } catch (error) {
                lastError = error;
                if (attempt < 3 && isTransientSendError(error?.message)) {
                    await sleep(800 * attempt);
                    continue;
                }
                break;
            }
        }

        if (!sent && lastError) {
            const errMessage = String(lastError?.message || "Gagal kirim file.");
            if (isTransientSendError(errMessage)) {
                return res.status(503).json({
                    ok: false,
                    message: "Sesi WhatsApp sedang transisi. Coba kirim ulang beberapa detik lagi.",
                });
            }

            return res.status(500).json({
                ok: false,
                message: errMessage,
            });
        }

        return res.json({
            ok: true,
            to: targetId,
            id: sent?.id?._serialized || null,
        });
    } catch (error) {
        return res.status(500).json({
            ok: false,
            message: error?.message || "Gagal kirim file.",
        });
    }
});

client.on("qr", async (qr) => {
    status = "qr";
    isClientReady = false;
    markClientUnhealthy();

    try {
        latestQrDataUrl = await QRCode.toDataURL(qr, { width: 320, margin: 0 });
        console.log("[wa] QR tersedia.");
    } catch (error) {
        console.error("[wa] gagal generate QR:", error?.message || error);
    }
});

client.on("authenticated", () => {
    status = "authenticated";
    reconnectAttempt = 0;
    clearReconnectTimer();
    markClientUnhealthy();
    console.log("[wa] authenticated");
});

client.on("ready", () => {
    status = "ready";
    isClientReady = true;
    latestQrDataUrl = "";
    reconnectAttempt = 0;
    clearReconnectTimer();
    markClientHealthy();
    console.log("[wa] ready");
});

client.on("auth_failure", (message) => {
    status = "auth_failure";
    isClientReady = false;
    markClientUnhealthy();
    console.error("[wa] auth failure:", message);
    scheduleReconnect("auth_failure");
});

client.on("disconnected", (reason) => {
    status = "disconnected";
    isClientReady = false;
    markClientUnhealthy();
    console.warn("[wa] disconnected:", reason);
    scheduleReconnect(`disconnected:${String(reason || "").toLowerCase()}`);
});

client.on("loading_screen", (percent, message) => {
    const pct = Number.isFinite(percent) ? `${percent}%` : String(percent ?? "");
    console.log(`[wa] loading ${pct} ${String(message || "").trim()}`.trim());
});

client.on("change_state", (nextState) => {
    const normalized = String(nextState || "").trim().toUpperCase();
    if (normalized) {
        console.log(`[wa] change_state: ${normalized}`);
    }

    if (normalized === "CONNECTED") {
        status = "ready";
        isClientReady = true;
        reconnectAttempt = 0;
        markClientHealthy();
        return;
    }

    if (["OPENING", "PAIRING", "TIMEOUT", "UNPAIRED", "UNPAIRED_IDLE", "CONFLICT"].includes(normalized)) {
        status = normalized.toLowerCase();
        isClientReady = false;
        markClientUnhealthy();
    }
});

client.on("message", async (message) => {
    const autoReply = loadRuntimeSettings();

    if (message.fromMe) {
        return;
    }

    const from = String(message.from || "");
    if (!from || from === "status@broadcast" || from.endsWith("@g.us")) {
        return;
    }

    if (autoReply.markAsSeenEnabled && isClientReady) {
        try {
            const chat = typeof message.getChat === "function" ? await message.getChat() : null;
            let marked = false;
            if (chat && typeof chat.sendSeen === "function") {
                await chat.sendSeen();
                marked = true;
            }
            if (!marked) {
                marked = await markIncomingAsSeen(from);
            }

            if (marked) {
                console.log(`[wa] chat ditandai terbaca: ${from}`);
            } else {
                console.warn(`[wa] gagal tandai terbaca: ${from}`);
            }
        } catch (error) {
            console.warn(`[wa] sendSeen gagal ke ${from}:`, error?.message || error);
        }
    }

    if (!autoReply.enabled || !isClientReady || !autoReply.text) {
        return;
    }

    const messageType = String(message.type || "").toLowerCase();
    const messageBody = String(message.body || "").trim();
    if (["e2e_notification", "notification_template", "protocol", "revoked"].includes(messageType)) {
        return;
    }

    if (!messageBody) {
        return;
    }

    const senderKey = toCanonicalSenderKey(from);
    if (!senderKey) {
        return;
    }

    const ignoredSenderKeys = new Set(
        (autoReply.ignoreNumbers || [])
            .map((entry) => toCanonicalSenderKey(entry))
            .filter(Boolean)
    );
    if (ignoredSenderKeys.has(senderKey)) {
        return;
    }

    const now = Date.now();
    const lastSentAt = autoReplyLastSentAt.get(senderKey) || 0;
    const cooldownMs = autoReply.cooldownSeconds * 1000;
    if (cooldownMs > 0 && now - lastSentAt < cooldownMs) {
        return;
    }

    try {
        const senderName = String(
            message?._data?.notifyName || message?._data?.pushname || message?.notifyName || ""
        ).trim();
        const senderNumber = senderKey;
        const replyText = String(autoReply.text)
            .replaceAll("{name}", senderName || senderNumber)
            .replaceAll("{number}", senderNumber)
            .replaceAll("{message}", messageBody)
            .trim();

        if (!replyText) {
            return;
        }

        let sent = false;
        let lastError = null;

        try {
            if (typeof message.reply === "function") {
                await message.reply(replyText);
                sent = true;
            }
        } catch (error) {
            lastError = error;
        }

        if (!sent) {
            try {
                await client.sendMessage(from, replyText);
                sent = true;
            } catch (error) {
                lastError = error;
            }
        }

        if (!sent) {
            try {
                await client.sendMessage(`${senderKey}@c.us`, replyText);
                sent = true;
            } catch (error) {
                lastError = error;
            }
        }

        if (!sent) {
            throw lastError || new Error("Gagal mengirim auto-reply.");
        }

        autoReplyLastSentAt.set(senderKey, now);
        console.log(`[wa] auto-reply terkirim ke ${from}`);
    } catch (error) {
        console.error(`[wa] auto-reply gagal ke ${from}:`, error?.message || error);
    }
});

process.on("unhandledRejection", (reason) => {
    console.error("[wa] unhandledRejection:", reason);
    scheduleReconnect("unhandled_rejection");
});

process.on("uncaughtException", (error) => {
    console.error("[wa] uncaughtException:", error?.message || error);
    scheduleReconnect("uncaught_exception");
});

const gracefulShutdown = async (signal) => {
    console.log(`[wa] shutdown signal ${signal} diterima, menutup gateway...`);
    clearReconnectTimer();
    clearServerRetryTimer();
    if (healthcheckTimer) {
        clearInterval(healthcheckTimer);
        healthcheckTimer = null;
    }

    if (server) {
        await new Promise((resolve) => {
            server.close(() => resolve());
        }).catch(() => null);
        server = null;
    }

    try {
        await client.destroy();
    } catch {
        // Ignore.
    }

    process.exit(0);
};

process.on("SIGINT", () => {
    gracefulShutdown("SIGINT").catch(() => process.exit(0));
});
process.on("SIGTERM", () => {
    gracefulShutdown("SIGTERM").catch(() => process.exit(0));
});

startHttpServer();
startHealthcheckLoop();

client.initialize().catch((error) => {
    status = "init_error";
    markClientUnhealthy();
    console.error("[wa] init error:", error?.message || error);
    scheduleReconnect("init_error");
});

