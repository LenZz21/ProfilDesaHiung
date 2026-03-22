@php
    $profileRecord = \App\Models\VillageProfile::query()->first();
    $whatsapp = trim((string) data_get($profileRecord, 'whatsapp', ''));

    $normalizePhone = static function (string $rawPhone): string {
        $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    };

    $waNumber = $normalizePhone($whatsapp);
    $waMessage = rawurlencode('Halo Admin Kampung, saya butuh bantuan layanan.');
    $waLink = $waNumber !== '' ? "https://wa.me/{$waNumber}?text={$waMessage}" : null;
@endphp

@if ($waLink)
    <span
        id="customer-service-fab-label"
        class="fixed bottom-9 right-20 z-[69] whitespace-nowrap rounded-full bg-slate-900/90 px-3 py-1.5 text-xs font-semibold text-white shadow-lg transition-all duration-500 ease-out opacity-0 translate-x-2 scale-95 pointer-events-none md:bottom-10 md:right-24"
    >
        Hubungi Admin
    </span>

    <a
        id="customer-service-fab"
        href="{{ $waLink }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Customer Service WhatsApp"
        title="Customer Service"
        data-scroll-threshold="24"
        class="fixed bottom-5 right-4 z-[70] inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-900/30 transition-all duration-700 ease-out opacity-0 translate-y-8 scale-90 pointer-events-none hover:-translate-y-0.5 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-300 focus:ring-offset-2 focus:ring-offset-transparent md:bottom-6 md:right-6"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M19.05 4.94A9.9 9.9 0 0 0 12.02 2C6.5 2 2 6.48 2 12c0 1.77.46 3.5 1.34 5.03L2 22l5.1-1.33A9.95 9.95 0 0 0 12.02 22C17.54 22 22 17.52 22 12c0-2.67-1.04-5.18-2.95-7.06zm-7.03 15.37a8.3 8.3 0 0 1-4.23-1.16l-.3-.18-3.02.79.81-2.95-.2-.31A8.23 8.23 0 0 1 3.7 12c0-4.58 3.73-8.31 8.32-8.31A8.27 8.27 0 0 1 20.3 12c0 4.58-3.72 8.31-8.28 8.31zm4.56-6.2c-.25-.13-1.47-.72-1.7-.8-.23-.08-.4-.13-.56.13-.16.25-.64.8-.78.96-.14.17-.28.19-.53.06a6.73 6.73 0 0 1-1.98-1.22 7.4 7.4 0 0 1-1.36-1.69c-.14-.25-.02-.38.11-.5.11-.11.25-.28.37-.42.12-.14.16-.25.24-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.48-.4-.41-.56-.42h-.48c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1s.9 2.44 1.02 2.61c.13.17 1.78 2.73 4.32 3.83.6.26 1.07.42 1.44.54.6.19 1.15.17 1.58.1.48-.07 1.47-.6 1.68-1.17.21-.58.21-1.07.15-1.17-.06-.1-.23-.16-.48-.29z"/>
        </svg>
    </a>

    <script>
        (function () {
            const fab = document.getElementById('customer-service-fab');
            const label = document.getElementById('customer-service-fab-label');
            if (!fab || !label) return;

            const threshold = Number(fab.dataset.scrollThreshold || 24);
            const showFabClass = ['opacity-100', 'translate-y-0', 'scale-100', 'pointer-events-auto'];
            const hideFabClass = ['opacity-0', 'translate-y-8', 'scale-90', 'pointer-events-none'];
            const showLabelClass = ['opacity-100', 'translate-x-0', 'scale-100'];
            const hideLabelClass = ['opacity-0', 'translate-x-2', 'scale-95', 'pointer-events-none'];
            let mobileLabelVisible = false;
            let lastMobileTapAt = 0;

            const isMobileMode = () => window.matchMedia('(hover: none), (pointer: coarse)').matches;

            const showLabel = () => {
                label.classList.remove(...hideLabelClass);
                label.classList.add(...showLabelClass);
                mobileLabelVisible = true;
            };

            const hideLabel = () => {
                label.classList.remove(...showLabelClass);
                label.classList.add(...hideLabelClass);
                mobileLabelVisible = false;
            };

            const toggleLabel = () => {
                if (mobileLabelVisible) {
                    hideLabel();
                    return;
                }

                showLabel();
            };

            const syncFabVisibility = () => {
                const shouldShow = window.scrollY > threshold;
                if (shouldShow) {
                    fab.classList.remove(...hideFabClass);
                    fab.classList.add(...showFabClass);
                    return;
                }

                fab.classList.remove(...showFabClass);
                fab.classList.add(...hideFabClass);
                hideLabel();
                lastMobileTapAt = 0;
            };

            fab.addEventListener('mouseenter', () => {
                if (!isMobileMode()) showLabel();
            });

            fab.addEventListener('mouseleave', () => {
                if (!isMobileMode()) hideLabel();
            });

            fab.addEventListener('click', (event) => {
                if (!isMobileMode()) {
                    return;
                }

                event.preventDefault();
                const now = Date.now();
                const doubleTapWindowMs = 280;

                if (lastMobileTapAt > 0 && now - lastMobileTapAt <= doubleTapWindowMs) {
                    lastMobileTapAt = 0;
                    hideLabel();
                    window.open(fab.href, '_blank', 'noopener,noreferrer');
                    return;
                }

                lastMobileTapAt = now;
                toggleLabel();
            });

            document.addEventListener('click', (event) => {
                if (!isMobileMode() || !mobileLabelVisible) {
                    return;
                }

                const target = event.target;
                if (fab.contains(target) || label.contains(target)) {
                    return;
                }

                hideLabel();
                lastMobileTapAt = 0;
            });

            window.addEventListener('scroll', syncFabVisibility, { passive: true });
            syncFabVisibility();
        })();
    </script>
@endif
