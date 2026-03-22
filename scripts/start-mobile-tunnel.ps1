param(
    [int]$Port = 8000
)

Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$ProjectRoot = Split-Path -Parent $ScriptDir
$StorageDir = Join-Path $ProjectRoot "storage"
$FrameworkDir = Join-Path $StorageDir "framework"
$LogsDir = Join-Path $StorageDir "logs"
$TunnelLogsDir = Join-Path $LogsDir "tunnel"
$ToolsDir = Join-Path $ProjectRoot "tools"
$StateFile = Join-Path $FrameworkDir "mobile-tunnel.json"
$HotFile = Join-Path $ProjectRoot "public\hot"
$HotBackupFile = Join-Path $FrameworkDir "mobile-vite-hot.backup"
$ServerLog = Join-Path $TunnelLogsDir "mobile-server.log"
$ServerErrorLog = Join-Path $TunnelLogsDir "mobile-server-error.log"
$TunnelLog = Join-Path $TunnelLogsDir "mobile-tunnel.log"
$TunnelErrorLog = Join-Path $TunnelLogsDir "mobile-tunnel-error.log"

New-Item -ItemType Directory -Force -Path $FrameworkDir | Out-Null
New-Item -ItemType Directory -Force -Path $TunnelLogsDir | Out-Null
New-Item -ItemType Directory -Force -Path $ToolsDir | Out-Null

if (Test-Path $StateFile) {
    $existing = Get-Content $StateFile -Raw | ConvertFrom-Json
    $existingServer = Get-Process -Id $existing.serverPid -ErrorAction SilentlyContinue
    $existingTunnel = Get-Process -Id $existing.tunnelPid -ErrorAction SilentlyContinue

    if ($null -ne $existingServer -or $null -ne $existingTunnel) {
        Write-Host "Mobile tunnel sudah berjalan."
        if ($existing.url) {
            Write-Host ("URL tunnel: {0}" -f $existing.url)
        }
        Write-Host "Jalankan: powershell -ExecutionPolicy Bypass -File .\scripts\stop-mobile-tunnel.ps1"
        exit 0
    }

    Remove-Item $StateFile -Force -ErrorAction SilentlyContinue
}

try {
    $phpCommand = Get-Command php -ErrorAction Stop
} catch {
    throw "PHP tidak ditemukan di PATH. Pastikan Laragon/PHP aktif."
}

function Stop-ViteDevServerIfRunning {
    param([int]$Port = 5173)

    $hasNetTcpCommand = Get-Command Get-NetTCPConnection -ErrorAction SilentlyContinue
    if ($null -eq $hasNetTcpCommand) {
        return
    }

    $listeners = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
    foreach ($listener in $listeners) {
        $process = Get-Process -Id $listener.OwningProcess -ErrorAction SilentlyContinue
        if ($null -eq $process) {
            continue
        }

        if ($process.ProcessName -eq "node") {
            Write-Host ("Menghentikan Vite dev server (PID {0}) agar tunnel mobile memakai aset build." -f $process.Id)
            Stop-Process -Id $process.Id -Force -ErrorAction SilentlyContinue
        } else {
            Write-Host ("Port {0} dipakai proses {1} (PID {2}). Pastikan Vite dev tidak aktif saat memakai tunnel." -f $Port, $process.ProcessName, $process.Id)
        }
    }
}

Stop-ViteDevServerIfRunning

$hotBackedUp = $false
if (Test-Path $HotFile) {
    Copy-Item -Path $HotFile -Destination $HotBackupFile -Force
    Remove-Item $HotFile -Force
    $hotBackedUp = $true
}

$CloudflaredPath = Join-Path $ToolsDir "cloudflared.exe"
if (-not (Test-Path $CloudflaredPath)) {
    $downloadUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
    Write-Host "Mengunduh cloudflared..."
    Invoke-WebRequest -Uri $downloadUrl -OutFile $CloudflaredPath
}

Remove-Item $ServerLog, $ServerErrorLog, $TunnelLog, $TunnelErrorLog -ErrorAction SilentlyContinue

$serverProcess = $null
$tunnelProcess = $null

function Stop-IfRunning {
    param([System.Diagnostics.Process]$Process)
    if ($null -eq $Process) {
        return
    }

    try {
        if (-not $Process.HasExited) {
            Stop-Process -Id $Process.Id -Force -ErrorAction SilentlyContinue
        }
    } catch {
        # Abaikan error saat cleanup.
    }
}

try {
    $serverProcess = Start-Process `
        -FilePath $phpCommand.Source `
        -ArgumentList @("artisan", "serve", "--host=127.0.0.1", "--port=$Port") `
        -WorkingDirectory $ProjectRoot `
        -RedirectStandardOutput $ServerLog `
        -RedirectStandardError $ServerErrorLog `
        -PassThru

    $serverReady = $false
    for ($i = 0; $i -lt 30; $i++) {
        Start-Sleep -Seconds 1
        if ($serverProcess.HasExited) {
            break
        }

        try {
            Invoke-WebRequest -Uri ("http://127.0.0.1:{0}" -f $Port) -UseBasicParsing -TimeoutSec 2 | Out-Null
            $serverReady = $true
            break
        } catch {
            # Tunggu sampai server siap.
        }
    }

    if (-not $serverReady) {
        throw "Laravel server gagal start. Cek log: $ServerLog"
    }

    $tunnelProcess = Start-Process `
        -FilePath $CloudflaredPath `
        -ArgumentList @("tunnel", "--url", "http://127.0.0.1:$Port", "--protocol", "http2", "--no-autoupdate", "--loglevel", "info") `
        -WorkingDirectory $ProjectRoot `
        -RedirectStandardOutput $TunnelLog `
        -RedirectStandardError $TunnelErrorLog `
        -PassThru

    $publicUrl = $null
    for ($i = 0; $i -lt 60; $i++) {
        Start-Sleep -Seconds 1

        $combinedLog = @()
        if (Test-Path $TunnelLog) {
            $combinedLog += Get-Content $TunnelLog -Raw
        }
        if (Test-Path $TunnelErrorLog) {
            $combinedLog += Get-Content $TunnelErrorLog -Raw
        }
        $match = [regex]::Match(($combinedLog -join "`n"), "https://[a-zA-Z0-9-]+\.trycloudflare\.com")
        if ($match.Success) {
            $publicUrl = $match.Value
            break
        }

        if ($tunnelProcess.HasExited) {
            break
        }
    }

    if (-not $publicUrl) {
        throw "Gagal membuat tunnel. Cek log: $TunnelErrorLog"
    }

    $state = @{
        serverPid = $serverProcess.Id
        tunnelPid = $tunnelProcess.Id
        port = $Port
        url = $publicUrl
        hotBackedUp = $hotBackedUp
        startedAt = (Get-Date).ToString("o")
    }

    $state | ConvertTo-Json | Set-Content -Path $StateFile

    Write-Host ""
    Write-Host "Tunnel siap dipakai di Android/iPhone:"
    Write-Host $publicUrl
    Write-Host ""
    Write-Host ("Local URL: http://127.0.0.1:{0}" -f $Port)
    Write-Host "Log server : $ServerLog"
    Write-Host "Log tunnel : $TunnelErrorLog"
    if ($hotBackedUp) {
        Write-Host "Mode mobile aktif: Vite dev (`public/hot`) dinonaktifkan sementara."
    }
    Write-Host ""
    Write-Host "Untuk stop:"
    Write-Host "powershell -ExecutionPolicy Bypass -File .\scripts\stop-mobile-tunnel.ps1"
} catch {
    Stop-IfRunning -Process $tunnelProcess
    Stop-IfRunning -Process $serverProcess
    throw
}
