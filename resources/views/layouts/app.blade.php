<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', in_array(app()->getLocale(), ['id', 'en'], true) ? app()->getLocale() : 'id') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $seoTitle ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seoDescription ?? __('Website profil desa.') }}">
    <script>
        (function () {
            const storageKey = 'profil-desa-theme';
            const root = document.documentElement;
            let theme = 'light';
            let shouldPersistTheme = false;
            let isInternalNavigation = false;

            try {
                if (document.referrer) {
                    const referrerUrl = new URL(document.referrer);
                    isInternalNavigation = referrerUrl.origin === window.location.origin;
                }
            } catch (error) {
                isInternalNavigation = false;
            }

            try {
                if (isInternalNavigation) {
                    const savedTheme = localStorage.getItem(storageKey);
                    if (savedTheme === 'light' || savedTheme === 'dark') {
                        theme = savedTheme;
                    } else {
                        shouldPersistTheme = true;
                    }
                } else {
                    shouldPersistTheme = true;
                }
            } catch (error) {
                // Abaikan jika localStorage tidak tersedia.
            }

            root.setAttribute('data-theme', theme);
            root.classList.toggle('theme-dark', theme === 'dark');
            root.style.colorScheme = theme;

            if (! shouldPersistTheme) {
                return;
            }

            try {
                localStorage.setItem(storageKey, theme);
            } catch (error) {
                // Abaikan jika localStorage tidak tersedia.
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-shell page-{{ str_replace('.', '-', request()->route()?->getName() ?? 'unknown') }} min-h-screen bg-slate-50 text-slate-800 antialiased">
    <x-navbar />

    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-footer />
    <x-customer-service-fab />
</body>
</html>
