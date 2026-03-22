@php
    $isHome = request()->routeIs('home');
    $isProfile = request()->routeIs('profile.*');
    $isProfileIndex = request()->routeIs('profile.index');
    $isInfographic = request()->routeIs('infographics.*');
    $isInfographicIndex = request()->routeIs('infographics.index');
    $isEvent = request()->routeIs('events.*');
    $isService = request()->routeIs('services.*');
    $isPost = request()->routeIs('posts.*');
    $isGallery = request()->routeIs('galleries.*');
    $isDocument = request()->routeIs('documents.*');

    $transparentOnTop = $isHome
        || $isProfileIndex
        || $isInfographicIndex
        || $isEvent
        || $isService
        || $isPost
        || $isGallery
        || $isDocument;

    $currentLocale = app()->getLocale();
    if (! in_array($currentLocale, ['id', 'en'], true)) {
        $currentLocale = 'id';
    }

    $nextLocale = $currentLocale === 'id' ? 'en' : 'id';
    $nextLanguageName = $nextLocale === 'id'
        ? __('ui.language_indonesia')
        : __('ui.language_english');
    $localeSwitchUrl = route('locale.switch', [
        'locale' => $nextLocale,
        'redirect' => url()->full(),
    ]);

    $informationLinks = [
        ['label' => __('ui.news'), 'route' => route('posts.index'), 'active' => $isPost],
        ['label' => __('ui.agenda'), 'route' => route('events.index'), 'active' => $isEvent],
        ['label' => __('ui.population_infographics'), 'route' => route('infographics.index'), 'active' => $isInfographic],
    ];
    $informationActive = collect($informationLinks)->contains(fn ($link) => $link['active']);

    $navLinks = [
        ['label' => __('ui.home'), 'route' => route('home'), 'active' => $isHome],
        ['label' => __('ui.village_profile'), 'route' => route('profile.index'), 'active' => $isProfile],
        ['label' => __('ui.services'), 'route' => route('services.index'), 'active' => $isService],
        ['label' => __('ui.gallery'), 'route' => route('galleries.index'), 'active' => $isGallery],
        ['label' => __('ui.publications'), 'route' => route('documents.index'), 'active' => $isDocument],
    ];

    $profileName = trim((string) data_get($profile ?? null, 'name', __('Kampung Hiung')));
    $villageName = preg_replace('/^(desa|kampung)\s+/i', '', $profileName) ?: $profileName;
@endphp

<nav
    id="site-navbar"
    class="{{ $transparentOnTop ? 'fixed left-3 right-3 top-3 rounded-2xl navbar-smooth sm:left-4 sm:right-4 sm:top-4' : 'sticky top-3 mx-3 rounded-2xl navbar-solid sm:mx-4 sm:top-4' }} z-50 text-white"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-slate-400 to-slate-600 text-xs font-black text-white shadow-lg">DS</span>
                <span class="text-sm font-bold leading-tight sm:text-base">
                    {{ __('ui.government_of_village') }}<br>{{ $villageName }}
                </span>
            </a>

            <div class="navbar-action-group flex items-center gap-1.5 sm:gap-2 lg:gap-3 xl:gap-4">
                <ul id="desktop-nav-menu" class="relative hidden items-center gap-4 text-[15px] font-semibold lg:flex xl:gap-8 xl:text-base 2xl:gap-10">
                    @foreach (array_slice($navLinks, 0, 2) as $link)
                        <li>
                            <a
                                href="{{ $link['route'] }}"
                                data-nav-link
                                data-active="{{ $link['active'] ? 'true' : 'false' }}"
                                class="block pb-2 text-white/95 transition-colors hover:text-white focus-visible:outline-none focus-visible:text-white {{ $link['active'] ? 'text-white' : '' }}"
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach

                    <li class="info-dropdown relative" data-info-dropdown>
                        <button
                            type="button"
                            data-info-trigger
                            data-nav-link
                            data-active="{{ $informationActive ? 'true' : 'false' }}"
                            aria-expanded="false"
                            aria-controls="desktop-info-panel"
                            aria-haspopup="true"
                            class="flex items-center gap-2 pb-2 text-white/95 transition-colors hover:text-white focus-visible:outline-none focus-visible:text-white {{ $informationActive ? 'text-white' : '' }}"
                        >
                            <span>{{ __('ui.information') }}</span>
                            <svg data-info-chevron class="h-4 w-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div id="desktop-info-panel" data-info-panel class="info-dropdown-panel">
                            <div class="info-dropdown-card">
                                <ul class="space-y-2">
                                    @foreach ($informationLinks as $link)
                                        <li>
                                            <a
                                                href="{{ $link['route'] }}"
                                                class="nav-sub-link {{ $link['active'] ? 'is-active' : '' }}"
                                            >
                                                {{ $link['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </li>

                    @foreach (array_slice($navLinks, 2) as $link)
                        <li>
                            <a
                                href="{{ $link['route'] }}"
                                data-nav-link
                                data-active="{{ $link['active'] ? 'true' : 'false' }}"
                                class="block pb-2 text-white/95 transition-colors hover:text-white focus-visible:outline-none focus-visible:text-white {{ $link['active'] ? 'text-white' : '' }}"
                            >
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                    <span data-nav-indicator class="nav-indicator"></span>
                </ul>

                <a
                    href="{{ $localeSwitchUrl }}"
                    aria-label="{{ __('ui.switch_language_to', ['language' => $nextLanguageName]) }}"
                    title="{{ __('ui.switch_language_to', ['language' => $nextLanguageName]) }}"
                    class="lang-switch inline-flex h-10 shrink-0 items-center px-1 text-white/95 transition hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                >
                    <span class="sr-only">{{ __('ui.switch_language') }}</span>
                    <span data-lang-flag-wrap class="h-5 w-7 overflow-hidden rounded-sm ring-1 ring-white/70">
                        @if ($currentLocale === 'id')
                            <svg data-lang-flag class="h-full w-full" viewBox="0 0 24 16" aria-hidden="true">
                                <rect width="24" height="8" fill="#ef4444"></rect>
                                <rect y="8" width="24" height="8" fill="#ffffff"></rect>
                            </svg>
                        @else
                            <svg data-lang-flag class="h-full w-full" viewBox="0 0 24 16" aria-hidden="true">
                                <rect width="24" height="16" fill="#0a2f73"></rect>
                                <polygon points="0,0 2.6,0 24,13.6 24,16 21.4,16 0,2.4" fill="#ffffff"></polygon>
                                <polygon points="24,0 21.4,0 0,13.6 0,16 2.6,16 24,2.4" fill="#ffffff"></polygon>
                                <polygon points="0,0 1.35,0 24,14.1 24,16 22.65,16 0,1.9" fill="#c8102e"></polygon>
                                <polygon points="24,0 22.65,0 0,14.1 0,16 1.35,16 24,1.9" fill="#c8102e"></polygon>
                                <rect x="9.5" width="5" height="16" fill="#ffffff"></rect>
                                <rect y="5.5" width="24" height="5" fill="#ffffff"></rect>
                                <rect x="10.4" width="3.2" height="16" fill="#c8102e"></rect>
                                <rect y="6.4" width="24" height="3.2" fill="#c8102e"></rect>
                            </svg>
                        @endif
                    </span>
                </a>

                <button
                    type="button"
                    data-theme-toggle
                    aria-label="{{ __('ui.enable_dark_mode') }}"
                    aria-pressed="false"
                    data-theme-dark-label="{{ __('ui.dark_mode') }}"
                    data-theme-light-label="{{ __('ui.light_mode') }}"
                    data-theme-activate-label="{{ __('ui.activate') }}"
                    class="theme-toggle-btn relative inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-white/95 transition hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/75"
                >
                    <svg data-theme-icon-light class="pointer-events-none absolute inset-0 m-auto h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.8M12 19.2V21M4.8 12H3M21 12h-1.8M6.35 6.35l-1.3-1.3M18.95 18.95l-1.3-1.3M17.65 6.35l1.3-1.3M5.05 18.95l1.3-1.3"/>
                        <circle cx="12" cy="12" r="4.2"/>
                    </svg>
                    <svg data-theme-icon-dark class="pointer-events-none absolute inset-0 m-auto h-7 w-7" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 14.1a8.2 8.2 0 1 1-10.9-10.9 7 7 0 1 0 10.9 10.9Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.8 3.9v1.4M16.8 8.5V9.9M14.9 6.9h-1.4M20.5 6.9h-1.4"/>
                    </svg>
                </button>

                <button
                    type="button"
                    data-mobile-toggle
                    aria-expanded="false"
                    aria-controls="mobile-nav-menu"
                    class="inline-flex items-center justify-center rounded-lg border border-white/35 bg-white/10 p-2 text-white/95 transition hover:bg-white/20 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/75 lg:hidden"
                >
                    <span class="sr-only">{{ __('ui.open_navigation_menu') }}</span>
                    <svg data-open-icon class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg data-close-icon class="hidden h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-nav-menu" data-mobile-menu class="mobile-nav-menu lg:hidden">
        <div class="mobile-nav-shell px-3 pb-4 pt-2 sm:px-6">
            <div class="mobile-nav-surface">
                <div class="space-y-1">
                    @foreach ($navLinks as $link)
                        <a
                            href="{{ $link['route'] }}"
                            class="mobile-nav-link {{ $link['active'] ? 'is-active' : '' }}"
                            style="--mobile-item-index: {{ $loop->index }};"
                        >
                            <span>{{ $link['label'] }}</span>
                            <svg class="mobile-nav-link-arrow" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 6 6 6-6 6"/>
                            </svg>
                        </a>
                    @endforeach
                </div>

                <div class="mobile-info-group mt-2.5 {{ $informationActive ? 'is-open' : '' }}" data-mobile-info-group>
                    <button
                        type="button"
                        class="mobile-info-summary"
                        data-mobile-info-toggle
                        aria-expanded="{{ $informationActive ? 'true' : 'false' }}"
                        style="--mobile-item-index: {{ count($navLinks) }};"
                    >
                        <span>{{ __('ui.information') }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>

                    <div class="mobile-info-links">
                        <div class="mobile-info-links-inner">
                            @foreach ($informationLinks as $link)
                                <a
                                    href="{{ $link['route'] }}"
                                    class="mobile-info-link {{ $link['active'] ? 'is-active' : '' }}"
                                    style="--mobile-item-index: {{ $loop->index + count($navLinks) }}; --mobile-info-index: {{ $loop->index }};"
                                >
                                    <span>{{ $link['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    #site-navbar {
        --navbar-solid-from: var(--site-navbar-solid-from, #1e3a8a);
        --navbar-solid-to: var(--site-navbar-solid-to, #1d4ed8);
        --navbar-shadow: var(--site-navbar-shadow, 0 14px 32px rgba(15, 23, 42, 0.26));
        --navbar-scrolled-bg: var(--site-navbar-scrolled-bg, rgba(59, 130, 246, 0.92));
        --navbar-mobile-bg: linear-gradient(
            135deg,
            var(--site-navbar-mobile-from, rgba(30, 64, 175, 0.96)),
            var(--site-navbar-mobile-to, rgba(29, 78, 216, 0.88))
        );
        --navbar-mobile-border: var(--site-navbar-mobile-border, rgba(255, 255, 255, 0.16));
        --navbar-dropdown-bg: var(--site-navbar-dropdown-bg, rgba(255, 255, 255, 0.95));
        --navbar-dropdown-shadow: var(--site-navbar-dropdown-shadow, 0 22px 42px rgba(15, 23, 42, 0.34));
        --navbar-dropdown-border: var(--site-navbar-dropdown-border, transparent);
        --navbar-sub-link: var(--site-navbar-sub-link, #334155);
        --navbar-sub-link-hover-bg: var(--site-navbar-sub-link-hover-bg, rgba(241, 245, 249, 0.95));
        --navbar-sub-link-hover-text: var(--site-navbar-sub-link-hover-text, #0f172a);
        --navbar-indicator: var(--site-navbar-indicator, #facc15);
        --navbar-indicator-shadow: var(--site-navbar-indicator-shadow, 0 0 8px rgba(250, 204, 21, 0.9), 0 0 18px rgba(250, 204, 21, 0.55));
        transition: transform .36s cubic-bezier(.22, 1, .36, 1), opacity .28s ease;
        will-change: transform;
    }

    #site-navbar.navbar-solid {
        background: linear-gradient(90deg, var(--navbar-solid-from), var(--navbar-solid-to));
        box-shadow: var(--navbar-shadow);
    }

    #site-navbar.navbar-smooth {
        background-color: transparent !important;
        transition:
            background-color .55s cubic-bezier(.22, 1, .36, 1),
            box-shadow .55s cubic-bezier(.22, 1, .36, 1),
            transform .36s cubic-bezier(.22, 1, .36, 1),
            opacity .28s ease;
    }

    #site-navbar.navbar-smooth.is-scrolled {
        background-color: var(--navbar-scrolled-bg) !important;
        box-shadow: var(--navbar-shadow);
    }

    #site-navbar.is-hidden-on-scroll {
        transform: translate3d(0, -130%, 0);
        opacity: 0;
        pointer-events: none;
    }

    #site-navbar .mobile-nav-menu {
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        pointer-events: none;
        border-top: 1px solid var(--navbar-mobile-border);
        border-inline: 1px solid var(--navbar-mobile-border);
        border-bottom: 1px solid var(--navbar-mobile-border);
        border-radius: 0 0 1rem 1rem;
        background: var(--navbar-mobile-bg);
        transform: translate3d(0, -8px, 0) scale(.985);
        transform-origin: top center;
        transition:
            max-height .62s cubic-bezier(.16, 1, .3, 1),
            opacity .34s ease,
            transform .62s cubic-bezier(.16, 1, .3, 1);
    }

    #site-navbar .mobile-nav-menu.is-open {
        max-height: min(74vh, 560px);
        opacity: 1;
        pointer-events: auto;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        transform: translate3d(0, 0, 0) scale(1);
    }

    @media (max-width: 1023.98px) {
        #site-navbar .navbar-action-group {
            gap: .6rem;
        }

        #site-navbar .mobile-nav-shell {
            padding-inline: .65rem;
        }

        #site-navbar .mobile-nav-surface {
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
            background:
                linear-gradient(160deg, rgba(15, 23, 42, 0.22), rgba(30, 41, 59, 0.12)),
                rgba(15, 23, 42, 0.16);
            padding: .45rem;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.28);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        #site-navbar .mobile-nav-link,
        #site-navbar .mobile-info-link {
            display: flex;
            min-height: 2.85rem;
            align-items: center;
            justify-content: space-between;
            border-radius: .75rem;
            padding: .65rem .75rem;
            font-size: .92rem;
            font-weight: 650;
            color: rgba(255, 255, 255, 0.92);
            transition: background-color .2s ease, color .2s ease, transform .2s ease;
        }

        #site-navbar .mobile-nav-link:hover,
        #site-navbar .mobile-nav-link:focus-visible,
        #site-navbar .mobile-info-link:hover,
        #site-navbar .mobile-info-link:focus-visible {
            background-color: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            outline: none;
        }

        #site-navbar .mobile-nav-link.is-active,
        #site-navbar .mobile-info-link.is-active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.14);
        }

        #site-navbar .mobile-nav-link-arrow {
            height: 1rem;
            width: 1rem;
            opacity: .78;
            transition: transform .2s ease, opacity .2s ease;
        }

        #site-navbar .mobile-nav-link:active,
        #site-navbar .mobile-info-link:active {
            transform: scale(.98);
        }

        #site-navbar .mobile-nav-link:hover .mobile-nav-link-arrow,
        #site-navbar .mobile-nav-link:focus-visible .mobile-nav-link-arrow {
            transform: translateX(2px);
            opacity: 1;
        }

        #site-navbar .mobile-info-group {
            border-radius: .85rem;
            border: 1px solid rgba(255, 255, 255, 0.16);
            background: rgba(15, 23, 42, 0.16);
            padding: .3rem;
        }

        #site-navbar .mobile-info-summary {
            display: flex;
            cursor: pointer;
            width: 100%;
            border: 0;
            background: transparent;
            align-items: center;
            justify-content: space-between;
            border-radius: .7rem;
            padding: .62rem .7rem;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: rgba(255, 255, 255, 0.78);
            transition: background-color .2s ease, color .2s ease;
        }

        #site-navbar .mobile-nav-link > span,
        #site-navbar .mobile-info-summary > span,
        #site-navbar .mobile-info-link > span {
            opacity: 0;
            transform: translate3d(0, 8px, 0);
            transition:
                opacity .34s ease,
                transform .42s cubic-bezier(.22, 1, .36, 1);
            transition-delay: 0s;
        }

        #site-navbar .mobile-info-summary:hover,
        #site-navbar .mobile-info-summary:focus-visible {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            outline: none;
        }

        #site-navbar .mobile-info-summary svg {
            transition: transform .46s cubic-bezier(.16, 1, .3, 1);
        }

        #site-navbar .mobile-info-group.is-open .mobile-info-summary svg {
            transform: rotate(180deg);
        }

        #site-navbar .mobile-info-links {
            display: grid;
            grid-template-rows: 0fr;
            margin-top: 0;
            opacity: 0;
            transform: translate3d(0, -8px, 0);
            transition:
                grid-template-rows .56s cubic-bezier(.16, 1, .3, 1),
                opacity .34s ease,
                transform .56s cubic-bezier(.16, 1, .3, 1),
                margin-top .56s cubic-bezier(.16, 1, .3, 1);
        }

        #site-navbar .mobile-info-links-inner {
            min-height: 0;
            overflow: hidden;
            display: grid;
            gap: .2rem;
            border-radius: .7rem;
            padding: 0 .24rem;
            background: rgba(255, 255, 255, 0.08);
            transition: padding .56s cubic-bezier(.16, 1, .3, 1), background-color .34s ease;
        }

        #site-navbar .mobile-info-group.is-open .mobile-info-links {
            grid-template-rows: 1fr;
            margin-top: .32rem;
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }

        #site-navbar .mobile-info-group.is-open .mobile-info-links-inner {
            padding: .24rem;
        }

        #site-navbar .mobile-info-links .mobile-info-link {
            opacity: 0;
            transform: translate3d(0, 8px, 0);
            transition: opacity .34s ease, transform .46s cubic-bezier(.16, 1, .3, 1);
        }

        #site-navbar .mobile-nav-menu.is-open.is-items-ready .mobile-nav-link,
        #site-navbar .mobile-nav-menu.is-open.is-items-ready .mobile-info-summary {
            animation: mobileNavItemIn .44s cubic-bezier(.22, 1, .36, 1) both;
            animation-delay: calc(var(--mobile-item-index, 0) * 44ms);
        }

        #site-navbar .mobile-nav-menu.is-items-ready .mobile-info-group.is-open .mobile-info-link {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            transition-delay: calc((var(--mobile-info-index, 0) * 74ms) + 140ms);
        }

        #site-navbar .mobile-nav-menu.is-items-ready .mobile-nav-link > span,
        #site-navbar .mobile-nav-menu.is-items-ready .mobile-info-summary > span,
        #site-navbar .mobile-nav-menu.is-items-ready .mobile-info-link > span {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            transition-delay: calc(var(--mobile-item-index, 0) * 44ms);
        }
    }

    #desktop-nav-menu .nav-indicator {
        position: absolute;
        left: 0;
        bottom: -2px;
        height: 3px;
        width: 0;
        border-radius: 9999px;
        background: var(--navbar-indicator);
        box-shadow: var(--navbar-indicator-shadow);
        transition: transform .42s cubic-bezier(.22, 1, .36, 1), width .42s cubic-bezier(.22, 1, .36, 1);
        pointer-events: none;
    }

    #desktop-nav-menu .info-dropdown-panel {
        --info-panel-duration: .56s;
        --info-panel-radius: 16px;
        position: absolute;
        top: calc(100% + 14px);
        left: calc(50% - 130px);
        width: 260px;
        transform: translate3d(0, -6px, 0);
        transform-origin: top center;
        clip-path: polygon(49.45% 49.1%, 50.55% 49.9%, 50.45% 50.9%, 49.35% 50.1%);
        overflow: visible;
        border-radius: 0;
        padding: 0;
        border: 0;
        background: transparent;
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
        opacity: 0;
        pointer-events: none;
        backface-visibility: hidden;
        contain: paint;
        will-change: transform, opacity;
    }

    #desktop-nav-menu .info-dropdown-card {
        overflow: hidden;
        border-radius: var(--info-panel-radius);
        padding: 14px;
        border: 1px solid transparent;
        background-clip: padding-box;
        background-color: var(--navbar-dropdown-bg);
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.18);
    }

    #desktop-nav-menu .info-dropdown-panel li {
        opacity: 0;
        transform: translateY(10px);
        transition:
            opacity .16s cubic-bezier(.33, 1, .68, 1),
            transform .18s cubic-bezier(.33, 1, .68, 1);
    }

    #desktop-nav-menu .info-dropdown.is-open .info-dropdown-panel {
        opacity: 1;
        transform: translate3d(0, 0, 0);
        clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        pointer-events: auto;
        animation: infoPanelOpen var(--info-panel-duration) cubic-bezier(.23, 1, .32, 1) both;
    }

    #desktop-nav-menu .info-dropdown.is-items-visible .info-dropdown-panel {
        clip-path: inset(0 1px 0 1px round var(--info-panel-radius));
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    #desktop-nav-menu .info-dropdown.is-items-visible .info-dropdown-card {
        box-shadow: var(--navbar-dropdown-shadow);
        border-color: var(--navbar-dropdown-border);
    }

    #desktop-nav-menu .info-dropdown.is-items-visible .info-dropdown-panel li {
        opacity: 1;
        transform: translateY(0);
    }

    #desktop-nav-menu .info-dropdown.is-closing .info-dropdown-panel {
        pointer-events: none;
        animation: infoPanelClose var(--info-panel-duration) cubic-bezier(.23, 1, .32, 1) both;
    }

    @keyframes infoPanelOpen {
        0% {
            opacity: 0;
            transform: translate3d(0, -6px, 0);
            clip-path: polygon(49.45% 49.1%, 50.55% 49.9%, 50.45% 50.9%, 49.35% 50.1%);
        }

        52% {
            opacity: 1;
            transform: translate3d(0, -2px, 0);
            clip-path: polygon(17% 15%, 18.6% 13.4%, 83% 85%, 81.4% 86.6%);
        }

        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
    }

    @keyframes infoPanelClose {
        0% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }

        46% {
            opacity: 1;
            transform: translate3d(0, -2px, 0);
            clip-path: polygon(17% 15%, 18.6% 13.4%, 83% 85%, 81.4% 86.6%);
        }

        100% {
            opacity: 0;
            transform: translate3d(0, -6px, 0);
            clip-path: polygon(49.45% 49.1%, 50.55% 49.9%, 50.45% 50.9%, 49.35% 50.1%);
        }
    }

    #desktop-nav-menu .info-dropdown.is-closing .info-dropdown-panel li {
        opacity: 0;
        transform: translateY(8px);
        transition-delay: 0s;
    }

    #desktop-nav-menu .info-dropdown.is-open [data-info-chevron],
    #desktop-nav-menu .info-dropdown.is-closing [data-info-chevron] {
        transform: rotate(180deg);
    }

    #desktop-nav-menu .nav-sub-link {
        display: block;
        border-radius: .75rem;
        padding: .5rem 1rem;
        font-size: 1.15rem;
        font-weight: 600;
        color: var(--navbar-sub-link);
        transition: color .24s ease, background-color .24s ease;
    }

    #desktop-nav-menu .nav-sub-link:hover,
    #desktop-nav-menu .nav-sub-link:focus-visible,
    #desktop-nav-menu .nav-sub-link.is-active {
        color: var(--navbar-sub-link-hover-text);
        background-color: var(--navbar-sub-link-hover-bg);
        outline: none;
    }

    #site-navbar .lang-switch {
        transform-origin: center;
    }

    #site-navbar .lang-switch [data-lang-flag-wrap] {
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    #site-navbar .lang-switch [data-lang-flag] {
        transition: transform .28s ease;
        transform-origin: left center;
    }

    #site-navbar .lang-switch:hover [data-lang-flag],
    #site-navbar .lang-switch:focus-visible [data-lang-flag] {
        animation: langFlagWave .65s ease;
    }

    #site-navbar .lang-switch:active {
        transform: scale(.95);
    }

    #site-navbar [data-theme-toggle] [data-theme-icon-light],
    #site-navbar [data-theme-toggle] [data-theme-icon-dark] {
        opacity: 0;
        transform: scale(.6) rotate(-42deg);
        transition: transform .34s cubic-bezier(.22, 1, .36, 1), opacity .24s ease;
    }

    #site-navbar [data-theme-toggle][data-theme-current="dark"] [data-theme-icon-dark] {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }

    #site-navbar [data-theme-toggle][data-theme-current="light"] [data-theme-icon-light] {
        opacity: 1;
        transform: scale(1) rotate(0deg);
    }

    #site-navbar [data-theme-toggle][data-theme-current="light"] [data-theme-icon-dark] {
        transform: scale(.6) rotate(42deg);
    }

    #site-navbar [data-theme-toggle].is-theme-switching {
        animation: themeTogglePop .34s cubic-bezier(.22, 1, .36, 1);
    }

    @keyframes themeTogglePop {
        0% { transform: scale(1); }
        45% { transform: scale(1.16) rotate(7deg); }
        100% { transform: scale(1); }
    }

    @keyframes langFlagWave {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(-5deg); }
        50% { transform: rotate(3deg); }
        75% { transform: rotate(-2deg); }
        100% { transform: rotate(0deg); }
    }

    @keyframes mobileNavItemIn {
        0% {
            opacity: 0;
            transform: translate3d(0, 8px, 0);
        }
        100% {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #site-navbar,
        #site-navbar .mobile-nav-menu,
        #site-navbar .mobile-nav-link,
        #site-navbar .mobile-info-link,
        #site-navbar .mobile-info-summary,
        #site-navbar .mobile-info-links,
        #site-navbar .mobile-info-links-inner,
        #site-navbar .mobile-nav-link > span,
        #site-navbar .mobile-info-summary > span,
        #site-navbar .mobile-info-link > span,
        #desktop-nav-menu .nav-indicator,
        #desktop-nav-menu .info-dropdown-panel,
        #desktop-nav-menu .info-dropdown-panel li,
        #site-navbar .lang-switch,
        #site-navbar .lang-switch [data-lang-flag],
        #site-navbar [data-theme-toggle] [data-theme-icon-light],
        #site-navbar [data-theme-toggle] [data-theme-icon-dark] {
            transition: none !important;
        }

        #desktop-nav-menu .info-dropdown-panel {
            animation: none !important;
        }

        #site-navbar .mobile-nav-menu.is-open .mobile-nav-link,
        #site-navbar .mobile-nav-menu.is-open .mobile-info-link,
        #site-navbar .mobile-nav-menu.is-open .mobile-info-summary {
            animation: none !important;
        }

        #site-navbar .mobile-info-summary svg {
            transition: none !important;
        }

        #desktop-nav-menu .info-dropdown.is-open .info-dropdown-panel {
            opacity: 1 !important;
            transform: translate3d(0, 0, 0) !important;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%) !important;
        }

        #desktop-nav-menu .info-dropdown.is-closing .info-dropdown-panel {
            opacity: 0 !important;
            transform: translate3d(0, -6px, 0) !important;
            clip-path: polygon(49.45% 49.1%, 50.55% 49.9%, 50.45% 50.9%, 49.35% 50.1%) !important;
        }

        #site-navbar .lang-switch:hover [data-lang-flag],
        #site-navbar .lang-switch:focus-visible [data-lang-flag] {
            animation: none !important;
        }

        #site-navbar [data-theme-toggle].is-theme-switching {
            animation: none !important;
        }
    }
</style>

<script>
    (function () {
        const navbar = document.getElementById('site-navbar');
        if (!navbar) {
            return;
        }

        const menu = document.getElementById('desktop-nav-menu');
        const mobileMenu = navbar.querySelector('[data-mobile-menu]');
        const mobileToggle = navbar.querySelector('[data-mobile-toggle]');
        const openIcon = mobileToggle?.querySelector('[data-open-icon]');
        const closeIcon = mobileToggle?.querySelector('[data-close-icon]');
        const mobileInfoGroup = mobileMenu?.querySelector('[data-mobile-info-group]');
        const mobileInfoToggle = mobileMenu?.querySelector('[data-mobile-info-toggle]');
        const root = document.documentElement;
        const themeStorageKey = 'profil-desa-theme';
        const themeButtons = Array.from(navbar.querySelectorAll('[data-theme-toggle]'));
        const themeSwitchTimers = new WeakMap();
        const hideOnScrollMobileMedia = window.matchMedia('(max-width: 1023.98px)');
        const navScrollFlagKey = 'profil-desa-force-top';
        const isTransparentOnTop = @json($transparentOnTop);
        let closeInfoMenu = () => {};
        let lastScrollY = Math.max(window.scrollY, 0);
        let isNavbarHidden = false;

        const scrollPageToTop = (behavior = 'auto') => {
            window.scrollTo({ top: 0, left: 0, behavior });

            if (behavior === 'smooth') {
                return;
            }

            document.documentElement.scrollTop = 0;
            document.body.scrollTop = 0;
        };

        if ('scrollRestoration' in window.history) {
            window.history.scrollRestoration = 'manual';
        }

        let shouldScrollToTopOnLoad = false;
        const navigationEntry = performance.getEntriesByType('navigation')[0];
        if (navigationEntry && (navigationEntry.type === 'reload' || navigationEntry.type === 'navigate')) {
            shouldScrollToTopOnLoad = true;
        }

        try {
            if (sessionStorage.getItem(navScrollFlagKey) === '1') {
                shouldScrollToTopOnLoad = true;
                sessionStorage.removeItem(navScrollFlagKey);
            }
        } catch (error) {
            // Abaikan jika sessionStorage tidak tersedia.
        }

        if (shouldScrollToTopOnLoad) {
            window.requestAnimationFrame(() => scrollPageToTop('auto'));
        }

        window.addEventListener('beforeunload', () => {
            try {
                sessionStorage.setItem(navScrollFlagKey, '1');
            } catch (error) {
                // Abaikan jika sessionStorage tidak tersedia.
            }
        });

        const readStoredTheme = () => {
            try {
                const storedTheme = localStorage.getItem(themeStorageKey);
                return storedTheme === 'light' || storedTheme === 'dark' ? storedTheme : null;
            } catch (error) {
                return null;
            }
        };

        const syncThemeButtonUI = (activeTheme) => {
            const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';

            themeButtons.forEach((button) => {
                const label = button.querySelector('[data-theme-label]');
                const darkThemeLabel = button.dataset.themeDarkLabel || 'Dark Mode';
                const lightThemeLabel = button.dataset.themeLightLabel || 'Light Mode';
                const activateLabel = button.dataset.themeActivateLabel || 'Activate';
                const nextThemeLabel = nextTheme === 'dark' ? darkThemeLabel : lightThemeLabel;

                if (label) {
                    label.textContent = nextThemeLabel;
                }

                button.dataset.themeCurrent = activeTheme;
                button.setAttribute('aria-label', `${activateLabel} ${nextThemeLabel.toLowerCase()}`);
                button.setAttribute('aria-pressed', activeTheme === 'dark' ? 'true' : 'false');
            });
        };

        const applyTheme = (theme, persist) => {
            root.setAttribute('data-theme', theme);
            root.classList.toggle('theme-dark', theme === 'dark');
            root.style.colorScheme = theme;
            syncThemeButtonUI(theme);

            if (!persist) {
                return;
            }

            try {
                localStorage.setItem(themeStorageKey, theme);
            } catch (error) {
                // Abaikan jika storage tidak tersedia.
            }
        };

        const initialTheme = (() => {
            const currentTheme = root.getAttribute('data-theme');
            if (currentTheme === 'light' || currentTheme === 'dark') {
                return currentTheme;
            }

            return readStoredTheme() ?? 'light';
        })();

        applyTheme(initialTheme, false);

        themeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const activeTheme = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
                const previousTimer = themeSwitchTimers.get(button);

                if (previousTimer) {
                    window.clearTimeout(previousTimer);
                }

                button.classList.remove('is-theme-switching');
                void button.offsetWidth;
                button.classList.add('is-theme-switching');

                const timerId = window.setTimeout(() => {
                    button.classList.remove('is-theme-switching');
                }, 360);
                themeSwitchTimers.set(button, timerId);
                applyTheme(nextTheme, true);
            });
        });

        const setNavbarHidden = (isHidden) => {
            if (isNavbarHidden === isHidden) {
                return;
            }

            isNavbarHidden = isHidden;
            navbar.classList.toggle('is-hidden-on-scroll', isHidden);
        };

        const setMobileMenuState = (isOpen) => {
            if (!mobileMenu || !mobileToggle) {
                return;
            }

            mobileMenu.classList.toggle('is-open', isOpen);
            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            openIcon?.classList.toggle('hidden', isOpen);
            closeIcon?.classList.toggle('hidden', !isOpen);

            if (!isOpen) {
                mobileMenu.classList.remove('is-items-ready');
            } else if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                mobileMenu.classList.add('is-items-ready');
            }

            if (isTransparentOnTop) {
                if (isOpen) {
                    navbar.classList.add('is-scrolled');
                } else if (window.scrollY <= 30) {
                    navbar.classList.remove('is-scrolled');
                }
            }

            if (isOpen) {
                setNavbarHidden(false);
            }
        };

        const setMobileInfoState = (isOpen) => {
            if (!mobileInfoGroup || !mobileInfoToggle) {
                return;
            }

            mobileInfoGroup.classList.toggle('is-open', isOpen);
            mobileInfoToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        if (mobileMenu && mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                setMobileMenuState(!mobileMenu.classList.contains('is-open'));
            });

            if (mobileInfoGroup && mobileInfoToggle) {
                mobileInfoToggle.addEventListener('click', () => {
                    setMobileInfoState(!mobileInfoGroup.classList.contains('is-open'));
                });
            }

            mobileMenu.addEventListener('transitionend', (event) => {
                if (event.target !== mobileMenu || event.propertyName !== 'max-height') {
                    return;
                }

                if (mobileMenu.classList.contains('is-open')) {
                    mobileMenu.classList.add('is-items-ready');
                }
            });

            mobileMenu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => setMobileMenuState(false));
            });

            document.addEventListener('click', (event) => {
                if (!mobileMenu.classList.contains('is-open')) {
                    return;
                }

                if (navbar.contains(event.target)) {
                    return;
                }

                setMobileMenuState(false);
            });

            const desktopMedia = window.matchMedia('(min-width: 1024px)');
            const handleDesktopMediaChange = (event) => {
                if (event.matches) {
                    setMobileMenuState(false);
                }
            };

            if (typeof desktopMedia.addEventListener === 'function') {
                desktopMedia.addEventListener('change', handleDesktopMediaChange);
            } else if (typeof desktopMedia.addListener === 'function') {
                desktopMedia.addListener(handleDesktopMediaChange);
            }
        }

        const wireNavbarScrollTop = () => {
            const navAnchors = Array.from(navbar.querySelectorAll('a[href]'));
            if (navAnchors.length === 0) {
                return;
            }

            navAnchors.forEach((link) => {
                link.addEventListener('click', (event) => {
                    if (
                        event.defaultPrevented ||
                        event.button !== 0 ||
                        event.metaKey ||
                        event.ctrlKey ||
                        event.shiftKey ||
                        event.altKey ||
                        link.target === '_blank' ||
                        link.hasAttribute('download')
                    ) {
                        return;
                    }

                    const href = link.getAttribute('href')?.trim() ?? '';
                    if (href === '' || href.startsWith('#') || href.startsWith('javascript:')) {
                        return;
                    }

                    try {
                        sessionStorage.setItem(navScrollFlagKey, '1');
                    } catch (error) {
                        // Abaikan jika sessionStorage tidak tersedia.
                    }

                    let targetUrl;
                    let currentUrl;

                    try {
                        targetUrl = new URL(link.href, window.location.href);
                        currentUrl = new URL(window.location.href);
                    } catch (error) {
                        return;
                    }

                    const isSamePage =
                        targetUrl.origin === currentUrl.origin &&
                        targetUrl.pathname === currentUrl.pathname &&
                        targetUrl.search === currentUrl.search;

                    if (!isSamePage) {
                        return;
                    }

                    event.preventDefault();
                    setMobileMenuState(false);
                    closeInfoMenu();

                    const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
                    scrollPageToTop(behavior);

                    if (targetUrl.hash) {
                        window.history.replaceState(null, '', `${targetUrl.pathname}${targetUrl.search}`);
                    }
                });
            });
        };

        if (menu) {
            const indicator = menu.querySelector('[data-nav-indicator]');
            const navItems = Array.from(menu.querySelectorAll('[data-nav-link]'));
            const infoDropdown = menu.querySelector('[data-info-dropdown]');
            const infoTrigger = infoDropdown?.querySelector('[data-info-trigger]');
            const infoPanel = infoDropdown?.querySelector('[data-info-panel]');

            if (infoDropdown && infoTrigger && infoPanel) {
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const closeDurationMs = prefersReducedMotion ? 0 : 560;
                const itemsRevealDelayMs = prefersReducedMotion ? 0 : closeDurationMs;
                const itemsHideDelayMs = prefersReducedMotion ? 0 : 110;

                let openItemsTimerId = null;
                let closeStartTimerId = null;
                let closeFinishTimerId = null;

                const clearInfoTimers = () => {
                    if (openItemsTimerId) {
                        window.clearTimeout(openItemsTimerId);
                        openItemsTimerId = null;
                    }

                    if (closeStartTimerId) {
                        window.clearTimeout(closeStartTimerId);
                        closeStartTimerId = null;
                    }

                    if (closeFinishTimerId) {
                        window.clearTimeout(closeFinishTimerId);
                        closeFinishTimerId = null;
                    }
                };

                const setInfoMenuState = (isOpen) => {
                    if (isOpen) {
                        clearInfoTimers();
                        infoDropdown.classList.remove('is-closing');
                        infoDropdown.classList.remove('is-items-visible');
                        infoDropdown.classList.add('is-open');
                        infoTrigger.setAttribute('aria-expanded', 'true');

                        openItemsTimerId = window.setTimeout(() => {
                            if (infoDropdown.classList.contains('is-open') && !infoDropdown.classList.contains('is-closing')) {
                                infoDropdown.classList.add('is-items-visible');
                            }
                            openItemsTimerId = null;
                        }, itemsRevealDelayMs);

                        return;
                    }

                    if (!infoDropdown.classList.contains('is-open') && !infoDropdown.classList.contains('is-closing')) {
                        return;
                    }

                    infoTrigger.setAttribute('aria-expanded', 'false');
                    clearInfoTimers();
                    const shouldDelayClose = infoDropdown.classList.contains('is-items-visible');
                    infoDropdown.classList.remove('is-items-visible');

                    closeStartTimerId = window.setTimeout(() => {
                        infoDropdown.classList.remove('is-open');
                        infoDropdown.classList.add('is-closing');

                        closeFinishTimerId = window.setTimeout(() => {
                            infoDropdown.classList.remove('is-closing');
                            closeFinishTimerId = null;
                        }, closeDurationMs);

                        closeStartTimerId = null;
                    }, shouldDelayClose ? itemsHideDelayMs : 0);
                };

                closeInfoMenu = () => setInfoMenuState(false);

                infoTrigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    setInfoMenuState(!infoDropdown.classList.contains('is-open'));
                });

                infoDropdown.addEventListener('focusout', (event) => {
                    if (!infoDropdown.contains(event.relatedTarget)) {
                        setInfoMenuState(false);
                    }
                });

                infoPanel.querySelectorAll('a').forEach((link) => {
                    link.addEventListener('click', () => setInfoMenuState(false));
                });

                document.addEventListener('click', (event) => {
                    if (!infoDropdown.contains(event.target)) {
                        setInfoMenuState(false);
                    }
                });
            }

            if (indicator && navItems.length) {
                let currentLink = navItems.find((item) => item.dataset.active === 'true') || navItems[0];
                const transitionValue = 'transform .42s cubic-bezier(.22, 1, .36, 1), width .42s cubic-bezier(.22, 1, .36, 1)';
                let resizeFrame = null;

                const moveIndicator = (targetLink) => {
                    if (!targetLink || !menu.offsetParent) {
                        return;
                    }

                    const menuRect = menu.getBoundingClientRect();
                    const linkRect = targetLink.getBoundingClientRect();
                    indicator.style.width = `${linkRect.width}px`;
                    indicator.style.transform = `translateX(${linkRect.left - menuRect.left}px)`;
                };

                const syncIndicatorWithoutAnimation = () => {
                    indicator.style.transition = 'none';
                    moveIndicator(currentLink);
                    requestAnimationFrame(() => {
                        indicator.style.transition = transitionValue;
                    });
                };

                syncIndicatorWithoutAnimation();

                navItems.forEach((item) => {
                    item.addEventListener('click', () => {
                        currentLink = item;
                        moveIndicator(currentLink);
                    });
                });

                window.addEventListener('resize', () => {
                    if (resizeFrame) {
                        cancelAnimationFrame(resizeFrame);
                    }

                    resizeFrame = requestAnimationFrame(() => {
                        syncIndicatorWithoutAnimation();
                        resizeFrame = null;
                    });
                }, { passive: true });
            }
        }

        wireNavbarScrollTop();

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            setMobileMenuState(false);
            closeInfoMenu();
        });

        const syncNavbarHideMode = () => {
            if (!hideOnScrollMobileMedia.matches) {
                setNavbarHidden(false);
            }

            lastScrollY = Math.max(window.scrollY, 0);
        };

        if (typeof hideOnScrollMobileMedia.addEventListener === 'function') {
            hideOnScrollMobileMedia.addEventListener('change', syncNavbarHideMode);
        } else if (typeof hideOnScrollMobileMedia.addListener === 'function') {
            hideOnScrollMobileMedia.addListener(syncNavbarHideMode);
        }

        const onScroll = () => {
            const currentScrollY = Math.max(window.scrollY, 0);
            const delta = currentScrollY - lastScrollY;
            const isMobileMenuOpen = mobileMenu?.classList.contains('is-open') ?? false;

            if (isTransparentOnTop) {
                if (currentScrollY > 30) {
                    navbar.classList.add('is-scrolled');
                } else {
                    navbar.classList.remove('is-scrolled');
                }
            }

            if (!hideOnScrollMobileMedia.matches) {
                setNavbarHidden(false);
                lastScrollY = currentScrollY;
                return;
            }

            if (isMobileMenuOpen || currentScrollY <= 12) {
                setNavbarHidden(false);
                lastScrollY = currentScrollY;
                return;
            }

            if (delta > 8) {
                closeInfoMenu();
                setNavbarHidden(true);
            } else if (delta < -8) {
                setNavbarHidden(false);
            }

            lastScrollY = currentScrollY;
        };

        syncNavbarHideMode();
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    })();
</script>
