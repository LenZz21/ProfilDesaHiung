<x-filament-panels::page wire:poll.8s="refreshGatewayStatus">
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Status Bot
            </x-slot>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Koneksi Gateway</p>
                    <p class="mt-1 text-lg font-semibold {{ $gatewayOnline ? 'text-success-600' : 'text-danger-600' }}">
                        {{ $gatewayOnline ? 'Online' : 'Offline' }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status Runtime</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ strtoupper($gatewayStatus) }}
                    </p>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">QR Tersedia</p>
                    <p class="mt-1 text-lg font-semibold {{ $qrAvailable ? 'text-success-600' : 'text-gray-700 dark:text-gray-200' }}">
                        {{ $qrAvailable ? 'Ya' : 'Tidak' }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                QR Login WhatsApp
            </x-slot>

            <p class="mb-4 text-sm text-gray-700 dark:text-gray-200">
                Scan QR dari HP: WhatsApp -> Perangkat Tertaut -> Tautkan Perangkat.
            </p>

            @if ($gatewayOnline && $qrAvailable)
                <div class="mx-auto w-full max-w-sm rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-950/40">
                    <div
                        class="mx-auto flex items-center justify-center rounded-xl bg-transparent p-0"
                        style="width: min(78vw, 260px); aspect-ratio: 1 / 1;"
                    >
                        <img
                            src="{{ $qrImageUrl }}"
                            alt="QR Login WhatsApp Bot"
                            class="h-full w-full rounded-lg object-contain"
                        />
                    </div>
                </div>
            @elseif ($gatewayOnline && strtolower($gatewayStatus) === 'ready')
                <div class="rounded-xl border border-success-200 bg-success-50 p-4 text-center text-sm text-success-700 dark:border-success-800 dark:bg-success-950/20 dark:text-success-300">
                    WhatsApp sudah terhubung. QR tidak diperlukan.
                </div>
            @elseif ($gatewayOnline)
                <div class="rounded-xl border border-warning-200 bg-warning-50 p-4 text-center text-sm text-warning-700 dark:border-warning-800 dark:bg-warning-950/20 dark:text-warning-300">
                    QR belum tersedia. Klik <strong>Refresh Status</strong> beberapa detik lagi.
                </div>
            @else
                <div class="rounded-xl border border-danger-200 bg-danger-50 p-4 text-center text-sm text-danger-700 dark:border-danger-800 dark:bg-danger-950/20 dark:text-danger-300">
                    Gateway belum online. Jalankan bot Node di port 3070 terlebih dahulu.
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Template Pesan Bot
            </x-slot>

            <form
                wire:submit="saveMessageTemplateSettings"
                class="space-y-4"
                x-data="{
                    async copyFrom(refName) {
                        const el = this.$refs[refName];
                        if (!el) return;

                        const text = String(el.value || '').trim();
                        if (!text) return;

                        const fallback = () => {
                            el.focus();
                            el.select();
                            document.execCommand('copy');
                        };

                        try {
                            if (navigator.clipboard && window.isSecureContext) {
                                await navigator.clipboard.writeText(text);
                            } else {
                                fallback();
                            }
                        } catch (e) {
                            fallback();
                        }
                    }
                }"
            >
                <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                    <input type="checkbox" wire:model.live="notifyOnLetterSubmission" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span>Aktifkan pesan otomatis saat pengajuan masuk</span>
                </label>

                <div class="space-y-1">
                    <div class="flex items-center justify-between gap-3">
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Pesan Pengajuan Masuk</label>
                        <button type="button" x-on:click="copyFrom('templateMasuk')" class="rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                            Salin
                        </button>
                    </div>
                    <textarea x-ref="templateMasuk" wire:model.defer="notifyLetterTemplate" rows="4" class="w-full select-text rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Template Terima</label>
                            <button type="button" x-on:click="copyFrom('templateTerima')" class="rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                Salin
                            </button>
                        </div>
                        <textarea x-ref="templateTerima" wire:model.defer="notifyLetterAcceptedTemplate" rows="4" class="w-full select-text rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    </div>
                    <div class="space-y-1">
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Template Selesai</label>
                            <button type="button" x-on:click="copyFrom('templateSelesai')" class="rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                Salin
                            </button>
                        </div>
                        <textarea x-ref="templateSelesai" wire:model.defer="notifyLetterDoneTemplate" rows="4" class="w-full select-text rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                            <input type="checkbox" wire:model.live="chatAutoReplyEnabled" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span>Aktifkan auto-reply untuk chat masuk umum (chat apa saja)</span>
                        </label>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-3">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Template Auto-Reply Chat</label>
                                <button type="button" x-on:click="copyFrom('templateChatAutoReply')" class="rounded-md border border-gray-300 px-3 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-800">
                                    Salin
                                </button>
                            </div>
                            <textarea x-ref="templateChatAutoReply" wire:model.defer="chatAutoReplyTemplate" rows="4" class="w-full select-text rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"></textarea>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Cooldown Auto-Reply (detik)</label>
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    wire:model.defer="chatAutoReplyCooldownSeconds"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-200">Nomor diabaikan (opsional)</label>
                                <input
                                    type="text"
                                    wire:model.defer="chatAutoReplyIgnoreNumbers"
                                    placeholder="Contoh: 08123456789, 628123456789"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                />
                            </div>
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Placeholder auto-reply chat: <code>{name}</code>, <code>{number}</code>, <code>{message}</code>.
                        </p>
                    </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Placeholder yang bisa dipakai: <code>{name}</code>, <code>{service_name}</code>, <code>{purpose}</code>, <code>{id}</code>, <code>{status}</code>.
                </p>

                <x-filament::button type="submit" color="primary">
                    Simpan Template Pesan
                </x-filament::button>
            </form>
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">
                Konfigurasi
            </x-slot>

            <p class="text-sm text-gray-700 dark:text-gray-300">
                Base URL yang dipakai: <code>{{ $gatewayBaseUrl }}</code>
            </p>
        </x-filament::section>
    </div>
</x-filament-panels::page>

