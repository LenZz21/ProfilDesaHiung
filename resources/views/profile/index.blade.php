@extends('layouts.app')

@section('content')
@php
    $missionItems = collect(preg_split('/\r\n|\r|\n/', (string) ($profile->mission ?? '')))
        ->map(fn ($item) => trim(preg_replace('/^\d+[\.\)]\s*/', '', $item)))
        ->filter()
        ->values();

    $historyChunks = collect(preg_split('/\n{2,}/', (string) ($profile->history ?? '')))
        ->map(fn ($item) => trim($item))
        ->filter()
        ->values();

    $historyTitles = collect([
        __('Sejarah Awal & Kerajaan Tabukan'),
        __('Asal Usul Nama Desa'),
        __('Julukan Masyarakat'),
        __('Pemerintahan Tradisional'),
        __('Masa Kolonial'),
        __('Pemekaran Desa'),
    ]);

    $historyItems = $historyChunks->values()->map(function ($content, $index) use ($historyTitles) {
        return [
            'title' => $historyTitles->get($index, __('Sejarah Desa')),
            'content' => $content,
        ];
    });

    if ($historyItems->isEmpty() && filled($profile?->history)) {
        $historyItems = collect([
            ['title' => __('Sejarah Desa'), 'content' => $profile->history],
        ]);
    }

    $officialCollection = collect($officials);
    $resolveGroup = function ($official) {
        return $official->structure_group ?: \App\Models\Official::detectStructureGroupFromPosition($official->position);
    };
    $leader = $officialCollection->first(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_LEADER);
    $secretary = $officialCollection->first(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_SECRETARY);
    $sectionHeadsPool = $officialCollection
        ->filter(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_SECTION_HEADS)
        ->values();
    $sectionHeads = $sectionHeadsPool->values();
    $kaurPool = $officialCollection
        ->filter(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_KAUR)
        ->values();
    $kaur = $kaurPool->values();
    $headLindongangPool = $officialCollection
        ->filter(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_HEAD_LINDONGANG)
        ->values();
    $headLindongang = $headLindongangPool->values();

    $resolveTitle = function ($customTitle, $fallback) {
        $title = trim((string) $customTitle);

        return $title !== '' ? $title : $fallback;
    };

    $centeredCardClass = function (int $count, bool $isLast): string {
        if ($count % 2 === 1 && $isLast) {
            return 'sm:col-span-2 sm:mx-auto sm:w-full sm:max-w-[20.5rem]';
        }

        return '';
    };

    $fixedGroupKeys = collect([
        \App\Models\Official::GROUP_LEADER,
        \App\Models\Official::GROUP_SECRETARY,
        \App\Models\Official::GROUP_SECTION_HEADS,
        \App\Models\Official::GROUP_KAUR,
        \App\Models\Official::GROUP_HEAD_LINDONGANG,
        \App\Models\Official::GROUP_OTHER,
    ]);

    $groupOptions = collect($structureGroupOptions ?? []);
    $groupedOfficials = $officialCollection->groupBy(fn ($o) => $resolveGroup($o));

    $configuredCustomGroupTitles = $groupOptions
        ->reject(fn ($title, $groupKey) => $fixedGroupKeys->contains($groupKey))
        ->map(fn ($title) => trim((string) $title))
        ->filter(fn ($title) => $title !== '');

    $detectedCustomGroupTitles = $groupedOfficials
        ->keys()
        ->reject(fn ($groupKey) => $fixedGroupKeys->contains($groupKey))
        ->mapWithKeys(fn ($groupKey) => [$groupKey => str_replace('_', ' ', strtoupper((string) $groupKey))]);

    $customGroupTitles = $configuredCustomGroupTitles->union($detectedCustomGroupTitles);

    $customGroups = $customGroupTitles
        ->map(function ($fallbackTitle, $groupKey) use ($groupedOfficials, $resolveTitle) {
            $items = collect($groupedOfficials->get($groupKey, collect()))->values();
            $displayTitle = $resolveTitle(
                $items->pluck('section_title')->filter(fn ($value) => filled($value))->first(),
                $fallbackTitle
            );

            return [
                'key' => $groupKey,
                'title' => $displayTitle,
                'officials' => $items,
            ];
        })
        ->values();

    $others = $officialCollection
        ->filter(fn ($o) => $resolveGroup($o) === \App\Models\Official::GROUP_OTHER)
        ->values();

    $leaderDisplayTitle = $resolveTitle($leader?->section_title, $structureTitles['leader_title'] ?? __('KEPALA DESA'));
    $secretaryDisplayTitle = $resolveTitle($secretary?->section_title, $structureTitles['secretary_title'] ?? __('SEKRETARIS DESA'));
    $sectionHeadsDisplayTitle = $resolveTitle(
        $sectionHeads->pluck('section_title')->filter(fn ($value) => filled($value))->first(),
        $structureTitles['section_heads_title'] ?? __('KEPALA SEKSI')
    );
    $kaurDisplayTitle = $resolveTitle(
        $kaur->pluck('section_title')->filter(fn ($value) => filled($value))->first(),
        $structureTitles['kaur_title'] ?? __('KAUR')
    );
    $headLindongangDisplayTitle = $resolveTitle(
        $headLindongang->pluck('section_title')->filter(fn ($value) => filled($value))->first(),
        $structureTitles['head_lindongang_title'] ?? __('KEPALA LINDONGANG')
    );

    $boundary = [
        ['title' => __('Utara'), 'value' => __('Wilayah Desa Tetangga'), 'accent' => '#22d3ee', 'rotation' => '-90deg'],
        ['title' => __('Timur'), 'value' => __('Wilayah Desa Tetangga'), 'accent' => '#60a5fa', 'rotation' => '0deg'],
        ['title' => __('Selatan'), 'value' => __('Wilayah Desa Tetangga'), 'accent' => '#f59e0b', 'rotation' => '90deg'],
        ['title' => __('Barat'), 'value' => __('Wilayah Desa Tetangga'), 'accent' => '#34d399', 'rotation' => '180deg'],
    ];

    $profileHeroBackground = collect([
        $profile?->home_background_image_1,
        $profile?->home_background_image_2,
        $profile?->home_background_image_3,
    ])->map(function ($path) {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
            ? $path
            : \Illuminate\Support\Facades\Storage::url($path);
    })->first(fn (?string $url) => filled($url))
        ?? 'https://images.pexels.com/photos/247431/pexels-photo-247431.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=1';

    $defaultProfileHeroTitle = __('Profil :village', ['village' => $profile->name ?? __('Desa')]);
    $defaultProfileHeroSubtitle = __('Mengenal lebih dekat visi misi, sejarah, dan potensi :village.', ['village' => $profile->name ?? __('desa')]);
    $configuredProfileHeroTitle = trim((string) data_get($profilePageSetting ?? [], 'title', ''));
    $configuredProfileHeroSubtitle = trim((string) data_get($profilePageSetting ?? [], 'subtitle', ''));
    $configuredProfileHeroImage = trim((string) data_get($profilePageSetting ?? [], 'hero_image_url', ''));
    $profileHeroTitle = $configuredProfileHeroTitle !== '' ? $configuredProfileHeroTitle : $defaultProfileHeroTitle;
    $profileHeroSubtitle = $configuredProfileHeroSubtitle !== '' ? $configuredProfileHeroSubtitle : $defaultProfileHeroSubtitle;
    $profileHeroBackground = $configuredProfileHeroImage !== '' ? $configuredProfileHeroImage : $profileHeroBackground;
@endphp

<style>
    [data-profile-map-menu-toggle] {
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    [data-profile-map-menu-panel] {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    :root[data-theme='dark'] [data-profile-map-menu-toggle] {
        border-color: rgba(125, 211, 252, 0.42);
        background: linear-gradient(180deg, rgba(7, 29, 58, 0.94), rgba(6, 24, 48, 0.94));
        color: #e5f0ff;
        box-shadow: inset 0 1px 0 rgba(186, 230, 253, 0.12), 0 10px 22px rgba(2, 6, 23, 0.34);
    }

    :root[data-theme='dark'] [data-profile-map-menu-toggle]:hover,
    :root[data-theme='dark'] [data-profile-map-menu-toggle]:focus-visible {
        border-color: rgba(125, 211, 252, 0.62);
        background: linear-gradient(180deg, rgba(9, 36, 72, 0.96), rgba(8, 29, 56, 0.96));
        color: #ffffff;
        outline: none;
    }

    :root[data-theme='dark'] [data-profile-map-menu-panel] {
        border-color: rgba(125, 211, 252, 0.44);
        background: linear-gradient(180deg, rgba(4, 22, 45, 0.98), rgba(3, 17, 35, 0.98));
        box-shadow: 0 18px 30px rgba(2, 6, 23, 0.46);
    }

    :root[data-theme='dark'] [data-profile-map-mode] {
        color: #cfe3ff;
        border-color: transparent;
        background-color: transparent;
    }

    :root[data-theme='dark'] [data-profile-map-mode]:hover,
    :root[data-theme='dark'] [data-profile-map-mode]:focus-visible {
        border-color: rgba(125, 211, 252, 0.38);
        background-color: rgba(56, 189, 248, 0.16);
        color: #ffffff;
        outline: none;
    }

    :root[data-theme='dark'] [data-profile-map-mode][aria-pressed='true'] {
        border-color: rgba(147, 197, 253, 0.56);
        background-color: rgba(37, 99, 235, 0.52);
        color: #ffffff;
    }

    @media (max-width: 767.98px) {
        [data-profile-map-menu] {
            display: flex;
            justify-content: flex-end;
        }

        .profile-map-menu-toggle {
            min-height: 2.45rem;
            border-radius: .78rem;
            padding: .56rem .72rem !important;
            font-size: .86rem !important;
            line-height: 1.15;
            border-color: rgba(59, 130, 246, 0.3);
            background: linear-gradient(180deg, rgba(247, 251, 255, 0.98), rgba(234, 244, 255, 0.98));
            color: #1e3a5f;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.16), inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .profile-map-menu-toggle svg:last-child {
            transition: transform .36s cubic-bezier(.16, 1, .3, 1);
        }

        .profile-map-menu-toggle[aria-expanded='true'] svg:last-child {
            transform: rotate(180deg);
        }

        .profile-map-menu-panel {
            width: min(208px, calc(100vw - 3.4rem));
            max-width: calc(100vw - 1.25rem);
            left: auto !important;
            right: 0 !important;
            border-radius: .78rem;
            border-color: rgba(96, 165, 250, 0.34);
            background: linear-gradient(180deg, #ffffff, #f3f8ff);
            padding: .36rem !important;
            box-shadow: 0 14px 26px rgba(15, 23, 42, 0.2);
        }

        .profile-map-mode-btn {
            min-height: 2.15rem;
            border-radius: .62rem !important;
            padding: .46rem .62rem !important;
            font-size: .92rem !important;
            font-weight: 650;
            border-color: transparent !important;
            background-color: transparent !important;
            color: #1e3a5f !important;
        }

        .profile-map-mode-btn + .profile-map-mode-btn {
            margin-top: .2rem !important;
        }

        .profile-map-mode-btn:hover,
        .profile-map-mode-btn:focus-visible {
            border-color: rgba(96, 165, 250, 0.42) !important;
            background-color: rgba(59, 130, 246, 0.12) !important;
            color: #0f172a !important;
            outline: none;
        }

        .profile-map-mode-btn[aria-pressed='true'] {
            border-color: rgba(96, 165, 250, 0.6) !important;
            background-color: rgba(37, 99, 235, 0.2) !important;
            color: #0f172a !important;
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.42);
        }

        .profile-map-mode-btn span:first-child {
            opacity: .95 !important;
        }

        :root[data-theme='dark'] .profile-map-menu-toggle {
            border-color: rgba(125, 211, 252, 0.46);
            background: linear-gradient(180deg, rgba(9, 36, 72, 0.96), rgba(7, 28, 54, 0.96));
            color: #e5f0ff;
            box-shadow: inset 0 1px 0 rgba(186, 230, 253, 0.14), 0 12px 22px rgba(2, 6, 23, 0.4);
        }

        :root[data-theme='dark'] .profile-map-menu-panel {
            border-color: rgba(125, 211, 252, 0.45);
            background: linear-gradient(180deg, rgba(4, 22, 45, 0.99), rgba(3, 17, 35, 0.99));
            box-shadow: 0 18px 30px rgba(2, 6, 23, 0.5);
        }

        :root[data-theme='dark'] .profile-map-mode-btn {
            color: #d4e5ff !important;
        }

        :root[data-theme='dark'] .profile-map-mode-btn:hover,
        :root[data-theme='dark'] .profile-map-mode-btn:focus-visible {
            border-color: rgba(125, 211, 252, 0.42) !important;
            background-color: rgba(56, 189, 248, 0.2) !important;
            color: #ffffff !important;
        }

        :root[data-theme='dark'] .profile-map-mode-btn[aria-pressed='true'] {
            border-color: rgba(147, 197, 253, 0.58) !important;
            background-color: rgba(37, 99, 235, 0.56) !important;
            color: #ffffff !important;
            box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.58);
        }
    }

    .profile-hero {
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    [data-profile-animate] {
        opacity: 0;
        transform: translate3d(0, 16px, 0);
        transition: opacity 560ms ease, transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: calc(var(--profile-animate-delay, 0ms) + 50ms);
    }

    [data-profile-animate][data-profile-animate-slow] {
        transform: translate3d(0, 14px, 0);
        transition: opacity 520ms ease, transform 660ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: calc(var(--profile-animate-delay, 0ms) + 30ms);
    }

    [data-profile-animate].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    @media (max-width: 1023.98px) {
        .profile-hero {
            height: clamp(280px, 48svh, 390px) !important;
            min-height: 280px;
            background-attachment: scroll;
            background-position: center;
        }
    }

    @media (max-width: 767.98px) and (orientation: landscape) {
        .profile-hero {
            height: clamp(230px, 80svh, 340px) !important;
            background-position: center 28%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        [data-profile-animate] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }
    }
</style>

<div class="profile-page">
<section
    class="profile-hero relative h-[300px] overflow-hidden bg-scroll bg-cover bg-center bg-no-repeat text-white sm:h-[360px] sm:bg-fixed"
    style="background-image: url('{{ $profileHeroBackground }}');"
>
    <div class="absolute inset-0 bg-slate-950/45"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/55 via-slate-900/35 to-emerald-950/55"></div>
    <div class="absolute inset-0">
        <div class="mx-auto flex h-full max-w-6xl items-center justify-center px-4 text-center sm:px-6">
            <div data-profile-animate>
                <h1 class="text-3xl font-black sm:text-4xl">{{ $profileHeroTitle }}</h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-100 sm:text-lg">
                    {{ $profileHeroSubtitle }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="profile-vision-section section-soft-separator bg-[#f3f4f6] py-16 sm:py-24">
    <div class="mx-auto max-w-6xl space-y-20 px-4 sm:space-y-24 sm:px-6">
        <div class="text-center" data-profile-animate style="--profile-animate-delay: 40ms;">
            <h2 class="profile-vision-heading text-slate-900">{{ __('Visi & Misi') }}</h2>
            <p class="profile-vision-subtitle mx-auto mt-4 max-w-3xl text-slate-600">{{ __('Landasan utama pembangunan kampung untuk mencapai kemajuan berkelanjutan.') }}</p>
            <span class="profile-section-line mt-4 inline-block"></span>
        </div>

        <div class="grid gap-8 lg:grid-cols-2" data-profile-animate style="--profile-animate-delay: 80ms;">
            <article class="profile-vision-card profile-vision-card--vision relative overflow-hidden rounded-xl border-l-4 border-blue-500 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8" data-profile-animate style="--profile-animate-delay: 110ms;">
                <h3 class="profile-vision-title flex items-center gap-3 font-black text-blue-700">
                    <svg class="h-9 w-9 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="9"></circle>
                        <circle cx="12" cy="12" r="4"></circle>
                        <circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"></circle>
                    </svg>
                    <span>{{ __('Visi') }}</span>
                </h3>
                <p class="profile-vision-quote mt-5 italic text-slate-700">"{{ $profile->vision ?? '-' }}"</p>
            </article>

            <article class="profile-vision-card profile-vision-card--mission relative overflow-hidden rounded-xl border-l-4 border-blue-500 bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8" data-profile-animate style="--profile-animate-delay: 170ms;">
                <h3 class="profile-vision-title flex items-center gap-3 font-black text-blue-700">
                    <svg class="h-9 w-9 text-yellow-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <polyline points="3 6 4.5 7.5 6.5 4.8"></polyline>
                        <polyline points="3 12 4.5 13.5 6.5 10.8"></polyline>
                        <polyline points="3 18 4.5 19.5 6.5 16.8"></polyline>
                    </svg>
                    <span>{{ __('Misi') }}</span>
                </h3>
                <div class="mt-5 space-y-4 sm:space-y-5">
                    @forelse ($missionItems as $index => $item)
                        <div class="profile-mission-item flex">
                            <span class="profile-mission-index w-9 shrink-0 font-black text-yellow-500">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <p class="profile-mission-text text-slate-700">{{ $item }}</p>
                        </div>
                    @empty
                        <p class="profile-mission-text text-slate-700">{{ $profile->mission ?? '-' }}</p>
                    @endforelse
                </div>
            </article>
        </div>

        <div class="pt-3 sm:pt-6" data-profile-animate style="--profile-animate-delay: 100ms;">
            <div class="text-center">
                <h2 class="profile-section-heading text-slate-900">{{ __('Sejarah :village', ['village' => $profile->name ?? __('Desa')]) }}</h2>
                <span class="profile-section-line mt-4 inline-block"></span>
            </div>

            <div class="mx-auto mt-12 max-w-5xl sm:mt-14">
                <div class="relative pl-7 sm:pl-12">
                    <div class="profile-history-line absolute left-2 top-1 h-[calc(100%-8px)] w-px sm:left-4"></div>
                    <div class="space-y-8 sm:space-y-10">
                        @forelse ($historyItems as $item)
                            <article class="relative" data-profile-animate style="--profile-animate-delay: {{ min($loop->index * 70, 280) }}ms;">
                                <span class="absolute -left-[1.8rem] top-2 h-3.5 w-3.5 rounded-full border-2 border-white bg-yellow-400 sm:-left-[2.25rem]"></span>
                                <h3 class="text-2xl font-black text-slate-900 sm:text-3xl">{{ $item['title'] }}</h3>
                                <p class="mt-2 text-sm leading-7 text-slate-700 sm:text-base sm:leading-8">{{ $item['content'] }}</p>
                            </article>
                        @empty
                            <p class="text-sm text-slate-600 sm:text-base">{{ __('Data sejarah belum tersedia.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-2 sm:pt-4" data-profile-animate style="--profile-animate-delay: 120ms;">
            <div class="text-center">
                <h2 class="profile-section-heading text-slate-900">{{ __('Peta Wilayah :village', ['village' => $profile->name ?? __('Desa')]) }}</h2>
                <p class="mx-auto mt-4 max-w-3xl text-base text-slate-600 sm:text-lg">{{ $profile->address ?? __('Alamat desa belum diisi.') }}</p>
                <span class="profile-section-line mt-4 inline-block"></span>
            </div>

            @php
                $profileMapUrl = trim((string) ($profile?->map_embed ?? ''));
                $profileMapUrl = $profileMapUrl !== '' ? html_entity_decode($profileMapUrl, ENT_QUOTES | ENT_HTML5) : '';
            @endphp

            <div class="mt-10 rounded-xl bg-white p-2.5 shadow-sm ring-1 ring-slate-200 sm:mt-12 sm:p-3" data-profile-animate style="--profile-animate-delay: 160ms;">
                @if ($profileMapUrl !== '')
                    <div class="relative h-[420px] w-full rounded-lg border border-slate-200" data-profile-map-surface>
                        <div class="pointer-events-none absolute right-3 top-3 z-[1200] flex items-start gap-2">
                            <div class="pointer-events-auto relative" data-profile-map-menu>
                                <button
                                    type="button"
                                    data-profile-map-menu-toggle
                                    aria-expanded="false"
                                    aria-controls="profile-map-mode-menu"
                                    class="profile-map-menu-toggle inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white/95 px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm transition hover:border-blue-300 hover:text-blue-700 sm:text-sm"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M3 8h18M3 12h18M3 16h18"></path>
                                    </svg>
                                    <span data-profile-map-current-label>Google Map</span>
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"></path>
                                    </svg>
                                </button>

                                <div
                                    id="profile-map-mode-menu"
                                    data-profile-map-menu-panel
                                    class="profile-map-menu-panel hidden absolute right-0 top-[calc(100%+0.45rem)] w-[188px] rounded-lg border border-slate-200 bg-white/98 p-1.5 shadow-lg"
                                >
                                    <button
                                        type="button"
                                        data-profile-map-mode="street"
                                        data-profile-map-label="Google Map"
                                        aria-pressed="true"
                                        class="profile-map-mode-btn flex w-full items-center gap-2 rounded-md border border-blue-600 bg-blue-50 px-2.5 py-1.5 text-left text-xs font-semibold text-blue-700 transition sm:text-sm"
                                    >
                                        <span class="inline-block h-2 w-2 rounded-full bg-current"></span>
                                        <span>Google Map</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-profile-map-mode="topographic"
                                        data-profile-map-label="Topographic"
                                        aria-pressed="false"
                                        class="profile-map-mode-btn mt-1 flex w-full items-center gap-2 rounded-md border border-transparent px-2.5 py-1.5 text-left text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:text-sm"
                                    >
                                        <span class="inline-block h-2 w-2 rounded-full bg-current opacity-45"></span>
                                        <span>Topographic</span>
                                    </button>
                                    <button
                                        type="button"
                                        data-profile-map-mode="satellite"
                                        data-profile-map-label="Satellite"
                                        aria-pressed="false"
                                        class="profile-map-mode-btn mt-1 flex w-full items-center gap-2 rounded-md border border-transparent px-2.5 py-1.5 text-left text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 sm:text-sm"
                                    >
                                        <span class="inline-block h-2 w-2 rounded-full bg-current opacity-45"></span>
                                        <span>Satellite</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <iframe
                            src="{{ $profileMapUrl }}"
                            data-profile-map-pane="street"
                            class="block h-full w-full rounded-[inherit]"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="{{ __('Google Maps Kampung') }}"
                        ></iframe>

                        <div
                            data-profile-map-pane="leaflet"
                            data-source-url="{{ $profileMapUrl }}"
                            data-zoom="18"
                            class="hidden h-full w-full rounded-[inherit]"
                            title="{{ __('Peta Alternatif Kampung') }}"
                        ></div>
                    </div>
                @else
                    <div class="grid h-[420px] place-items-center rounded-xl bg-slate-100 text-slate-500">{{ __('Peta belum tersedia.') }}</div>
                @endif
            </div>

            <div class="profile-boundary-panel mt-8 overflow-hidden rounded-2xl p-5 sm:mt-10 sm:p-7" data-profile-animate style="--profile-animate-delay: 200ms;">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="profile-boundary-title text-lg font-black sm:text-2xl">{{ __('Batas Wilayah') }}</h3>
                        <p class="profile-boundary-subtitle mt-1 text-xs sm:text-sm">{{ __('Arah perbatasan administratif Kampung :village.', ['village' => $profile->name ?? __('Desa')]) }}</p>
                    </div>
                    <span class="profile-boundary-badge inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3"></path>
                        </svg>
                        {{ __('4 Arah') }}
                    </span>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($boundary as $item)
                        <div class="profile-boundary-item group relative overflow-hidden rounded-xl p-3 text-center transition duration-300 hover:-translate-y-0.5 sm:p-4" data-profile-animate style="--profile-animate-delay: {{ 220 + ($loop->index * 45) }}ms;">
                            <span class="profile-boundary-item-line absolute inset-x-0 top-0 h-0.5"></span>

                            <div class="profile-boundary-icon-wrap mx-auto mb-2 inline-flex h-8 w-8 items-center justify-center rounded-lg sm:h-9 sm:w-9">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="transform: rotate({{ $item['rotation'] }});" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v15m0-15 4 4m-4-4-4 4"></path>
                                </svg>
                            </div>

                            <p class="profile-boundary-item-title text-sm font-black sm:text-base">{{ $item['title'] }}</p>
                            <p class="profile-boundary-item-value mt-1 text-[11px] sm:text-xs">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="profile-structure-section pt-8 sm:pt-10">
            <div class="profile-structure-head text-center" data-profile-animate data-profile-animate-slow style="--profile-animate-delay: 30ms;">
                <h2 class="profile-structure-heading text-slate-900">{{ __('Struktur Pemerintahan :village', ['village' => $profile->name ?? __('Desa')]) }}</h2>
                <span class="profile-structure-head-accent mt-5 inline-block"></span>
                <span class="profile-structure-head-link" aria-hidden="true"></span>
            </div>

            <div class="profile-structure-shell relative mx-auto mt-2 max-w-5xl pb-2 sm:mt-3" data-profile-animate style="--profile-animate-delay: 180ms;">
                <div class="profile-structure-axis"></div>

                <div class="profile-structure-list relative z-10 space-y-8">
                    @if ($leader)
                        <div class="profile-structure-group profile-structure-group--leader text-center" data-profile-animate style="--profile-animate-delay: 220ms;">
                            <span class="profile-structure-badge">{{ strtoupper($leaderDisplayTitle) }}</span>
                            <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                            <x-official-card :official="$leader" extra-class="profile-structure-card mx-auto mt-0 w-full max-w-xs" />
                        </div>
                    @endif

                    @if ($secretary)
                        <div class="profile-structure-group text-center" data-profile-animate style="--profile-animate-delay: 240ms;">
                            <span class="profile-structure-badge">{{ strtoupper($secretaryDisplayTitle) }}</span>
                            <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                            <x-official-card :official="$secretary" extra-class="profile-structure-card mx-auto mt-0 w-full max-w-xs" />
                        </div>
                    @endif

                    @if ($sectionHeads->isNotEmpty())
                        <div class="profile-structure-group text-center" data-profile-animate style="--profile-animate-delay: 260ms;">
                            <span class="profile-structure-badge">{{ strtoupper($sectionHeadsDisplayTitle) }}</span>
                            <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                            <div class="profile-structure-grid mx-auto mt-0 grid max-w-2xl gap-4 sm:grid-cols-2">
                                @foreach ($sectionHeads as $official)
                                    <x-official-card :official="$official" :extra-class="'profile-structure-card mx-auto w-full max-w-xs ' . $centeredCardClass($sectionHeads->count(), $loop->last)" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($kaur->isNotEmpty())
                        <div class="profile-structure-group text-center" data-profile-animate style="--profile-animate-delay: 280ms;">
                            <span class="profile-structure-badge">{{ strtoupper($kaurDisplayTitle) }}</span>
                            <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                            <div class="profile-structure-grid mx-auto mt-0 grid max-w-2xl gap-4 sm:grid-cols-2">
                                @foreach ($kaur as $official)
                                    <x-official-card :official="$official" :extra-class="'profile-structure-card mx-auto w-full max-w-xs ' . $centeredCardClass($kaur->count(), $loop->last)" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($headLindongang->isNotEmpty())
                        <div class="profile-structure-group text-center" data-profile-animate style="--profile-animate-delay: 300ms;">
                            <span class="profile-structure-badge">{{ strtoupper($headLindongangDisplayTitle) }}</span>
                            <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                            <div class="profile-structure-grid mx-auto mt-0 grid max-w-2xl gap-4 sm:grid-cols-2">
                                @foreach ($headLindongang as $official)
                                    <x-official-card :official="$official" :extra-class="'profile-structure-card mx-auto w-full max-w-xs ' . $centeredCardClass($headLindongang->count(), $loop->last)" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($customGroups->isNotEmpty())
                        @foreach ($customGroups as $group)
                            <div class="profile-structure-group text-center" data-profile-animate style="--profile-animate-delay: {{ 320 + ($loop->index * 40) }}ms;">
                                <span class="profile-structure-badge">{{ strtoupper($group['title']) }}</span>
                                @if ($group['officials']->isNotEmpty())
                                    <div class="profile-structure-joint mx-auto mt-1 h-10"></div>
                                    <div class="profile-structure-grid mx-auto mt-0 grid max-w-2xl gap-4 sm:grid-cols-2">
                                        @foreach ($group['officials'] as $official)
                                            <x-official-card :official="$official" :extra-class="'profile-structure-card mx-auto w-full max-w-xs ' . $centeredCardClass($group['officials']->count(), $loop->last)" />
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mx-auto mt-4 max-w-xl text-sm text-slate-300/90">{{ __('Belum ada aparatur pada bagian ini.') }}</p>
                                @endif
                            </div>
                        @endforeach
                    @endif

                    @if ($others->isNotEmpty())
                        <div class="profile-structure-grid mx-auto grid max-w-2xl gap-4 sm:grid-cols-2" data-profile-animate style="--profile-animate-delay: 360ms;">
                            @foreach ($others as $official)
                                <x-official-card :official="$official" :extra-class="'profile-structure-card mx-auto w-full max-w-xs ' . $centeredCardClass($others->count(), $loop->last)" />
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

</div>
<script>
    (() => {
        const targets = Array.from(document.querySelectorAll('[data-profile-animate]'));
        if (targets.length === 0) {
            return;
        }

        const visionCards = Array.from(document.querySelectorAll('.profile-vision-card'));
        const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        if (canHover) {
            visionCards.forEach((card) => {
                card.addEventListener('pointerenter', () => {
                    card.classList.add('is-hovered');
                });

                card.addEventListener('pointerleave', () => {
                    card.classList.remove('is-hovered');
                });

                card.addEventListener('pointercancel', () => {
                    card.classList.remove('is-hovered');
                });
            });
        }

        const initProfileMapMode = () => {
            const mapSurface = document.querySelector('[data-profile-map-surface]');
            if (!(mapSurface instanceof HTMLElement) || mapSurface.dataset.mapInitialized === 'true') {
                return;
            }

            const modeButtons = Array.from(mapSurface.querySelectorAll('[data-profile-map-mode]'));
            const mapMenu = mapSurface.querySelector('[data-profile-map-menu]');
            const menuToggle = mapSurface.querySelector('[data-profile-map-menu-toggle]');
            const menuPanel = mapSurface.querySelector('[data-profile-map-menu-panel]');
            const currentLabel = mapSurface.querySelector('[data-profile-map-current-label]');
            const streetPane = mapSurface.querySelector('[data-profile-map-pane="street"]');
            const leafletPane = mapSurface.querySelector('[data-profile-map-pane="leaflet"]');
            if (!(streetPane instanceof HTMLIFrameElement) || !(leafletPane instanceof HTMLElement) || modeButtons.length === 0) {
                return;
            }

            let leafletMapInitPromise = null;
            let leafletMap = null;
            let topographicLayer = null;
            let satelliteLayer = null;
            let activeLeafletMode = 'satellite';

            const parseLatLngFromMapUrl = (rawUrl) => {
                const sourceUrl = typeof rawUrl === 'string' ? rawUrl.trim() : '';
                if (sourceUrl === '') {
                    return null;
                }

                try {
                    const parsedUrl = new URL(sourceUrl, window.location.href);
                    const candidates = [
                        parsedUrl.searchParams.get('q'),
                        parsedUrl.searchParams.get('query'),
                        parsedUrl.searchParams.get('destination'),
                        parsedUrl.searchParams.get('center'),
                    ];

                    for (const candidate of candidates) {
                        const value = typeof candidate === 'string' ? candidate.trim() : '';
                        const match = value.match(/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/);
                        if (!match) {
                            continue;
                        }

                        const lat = Number.parseFloat(match[1]);
                        const lng = Number.parseFloat(match[2]);
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            return [lat, lng];
                        }
                    }

                    const atPathMatch = parsedUrl.pathname.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/);
                    if (atPathMatch) {
                        const lat = Number.parseFloat(atPathMatch[1]);
                        const lng = Number.parseFloat(atPathMatch[2]);
                        if (Number.isFinite(lat) && Number.isFinite(lng)) {
                            return [lat, lng];
                        }
                    }
                } catch (error) {
                    return null;
                }

                return null;
            };

            const loadLeafletAssets = () => {
                if (window.L && typeof window.L.map === 'function') {
                    return Promise.resolve(window.L);
                }

                if (window.__profileLeafletAssetPromise) {
                    return window.__profileLeafletAssetPromise;
                }

                window.__profileLeafletAssetPromise = new Promise((resolve, reject) => {
                    const cssId = 'leaflet-cdn-css';
                    if (!document.getElementById(cssId)) {
                        const css = document.createElement('link');
                        css.id = cssId;
                        css.rel = 'stylesheet';
                        css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        css.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
                        css.crossOrigin = '';
                        document.head.appendChild(css);
                    }

                    const scriptId = 'leaflet-cdn-js';
                    const finish = () => {
                        if (window.L && typeof window.L.map === 'function') {
                            resolve(window.L);
                            return;
                        }
                        reject(new Error('Leaflet gagal dimuat.'));
                    };

                    const existingScript = document.getElementById(scriptId);
                    if (existingScript instanceof HTMLScriptElement) {
                        existingScript.addEventListener('load', finish, { once: true });
                        existingScript.addEventListener('error', () => reject(new Error('Leaflet gagal dimuat.')), { once: true });
                        if (window.L) {
                            finish();
                        }
                        return;
                    }

                    const script = document.createElement('script');
                    script.id = scriptId;
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
                    script.crossOrigin = '';
                    script.async = true;
                    script.addEventListener('load', finish, { once: true });
                    script.addEventListener('error', () => reject(new Error('Leaflet gagal dimuat.')), { once: true });
                    document.head.appendChild(script);
                });

                return window.__profileLeafletAssetPromise;
            };

            const setButtonsState = (mode) => {
                modeButtons.forEach((button) => {
                    const isActive = button.dataset.profileMapMode === mode;
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    button.classList.toggle('border-blue-600', isActive);
                    button.classList.toggle('bg-blue-50', isActive);
                    button.classList.toggle('text-blue-700', isActive);
                    button.classList.toggle('border-transparent', !isActive);
                    button.classList.toggle('text-slate-700', !isActive);
                    button.classList.toggle('hover:border-slate-300', !isActive);
                    button.classList.toggle('hover:bg-slate-50', !isActive);

                    const indicatorDot = button.querySelector('span');
                    if (indicatorDot instanceof HTMLElement) {
                        indicatorDot.classList.toggle('opacity-45', !isActive);
                    }

                    if (isActive && currentLabel instanceof HTMLElement) {
                        currentLabel.textContent = button.dataset.profileMapLabel || button.textContent?.trim() || 'Google Map';
                    }
                });
            };

            const setMenuOpen = (open) => {
                if (!(menuPanel instanceof HTMLElement) || !(menuToggle instanceof HTMLElement)) {
                    return;
                }

                menuPanel.classList.toggle('hidden', !open);
                menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            };

            if (mapMenu instanceof HTMLElement && menuToggle instanceof HTMLElement && menuPanel instanceof HTMLElement) {
                menuToggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    const nextOpen = menuPanel.classList.contains('hidden');
                    setMenuOpen(nextOpen);
                });

                document.addEventListener('click', (event) => {
                    if (!(event.target instanceof Node)) {
                        return;
                    }

                    if (!mapMenu.contains(event.target)) {
                        setMenuOpen(false);
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        setMenuOpen(false);
                    }
                });
            }

            const switchLeafletLayer = (mode) => {
                if (!leafletMap || !topographicLayer || !satelliteLayer) {
                    return;
                }

                const nextMode = mode === 'topographic' ? 'topographic' : 'satellite';
                const nextLayer = nextMode === 'topographic' ? topographicLayer : satelliteLayer;
                const prevLayer = nextMode === 'topographic' ? satelliteLayer : topographicLayer;

                if (leafletMap.hasLayer(prevLayer)) {
                    leafletMap.removeLayer(prevLayer);
                }

                if (!leafletMap.hasLayer(nextLayer)) {
                    nextLayer.addTo(leafletMap);
                }

                activeLeafletMode = nextMode;
            };

            const ensureLeafletMap = () => {
                if (leafletMap) {
                    return Promise.resolve(leafletMap);
                }

                if (leafletMapInitPromise) {
                    return leafletMapInitPromise;
                }

                const sourceUrl = leafletPane.dataset.sourceUrl || '';
                const parsedCenter = parseLatLngFromMapUrl(sourceUrl);
                const center = Array.isArray(parsedCenter) ? parsedCenter : [3.543769, 125.527476];
                const rawZoom = Number.parseInt(leafletPane.dataset.zoom || '18', 10);
                const zoom = Number.isFinite(rawZoom) ? Math.min(Math.max(rawZoom, 4), 19) : 18;

                leafletMapInitPromise = loadLeafletAssets()
                    .then((L) => {
                        topographicLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                            maxZoom: 19,
                            detectRetina: true,
                            attribution: '&copy; Esri',
                        });

                        const satelliteImageryLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                            maxZoom: 21,
                            detectRetina: true,
                            subdomains: ['0', '1', '2', '3'],
                            attribution: '&copy; Google',
                        });

                        const satelliteLabelsLayer = L.tileLayer('https://mt{s}.google.com/vt/lyrs=h&x={x}&y={y}&z={z}', {
                            maxZoom: 21,
                            opacity: 0.95,
                            detectRetina: true,
                            subdomains: ['0', '1', '2', '3'],
                            attribution: '&copy; Google',
                        });

                        satelliteLayer = L.layerGroup([satelliteImageryLayer, satelliteLabelsLayer]);

                        leafletMap = L.map(leafletPane, {
                            zoomControl: true,
                            attributionControl: true,
                            zoomAnimation: true,
                            fadeAnimation: false,
                        });

                        const googleLikePin = L.icon({
                            iconUrl: 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi3.png',
                            iconSize: [27, 43],
                            iconAnchor: [13, 41],
                            popupAnchor: [1, -34],
                            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                            shadowSize: [41, 41],
                            shadowAnchor: [13, 41],
                        });

                        satelliteLayer.addTo(leafletMap);
                        activeLeafletMode = 'satellite';
                        L.marker(center, { icon: googleLikePin }).addTo(leafletMap);
                        leafletMap.setView(center, zoom);
                        window.setTimeout(() => leafletMap.invalidateSize(), 90);
                        return leafletMap;
                    })
                    .catch(() => {
                        leafletPane.innerHTML = '<div class="grid h-full place-items-center rounded-xl bg-slate-100 text-center text-sm text-slate-500">Peta alternatif gagal dimuat.</div>';
                        return null;
                    })
                    .finally(() => {
                        leafletMapInitPromise = null;
                    });

                return leafletMapInitPromise;
            };

            const activateMode = (mode) => {
                const nextMode = mode === 'topographic' || mode === 'satellite' ? mode : 'street';
                setButtonsState(nextMode);

                if (nextMode === 'street') {
                    streetPane.classList.remove('hidden');
                    leafletPane.classList.add('hidden');
                    return;
                }

                streetPane.classList.add('hidden');
                leafletPane.classList.remove('hidden');
                ensureLeafletMap().then((map) => {
                    if (!map) {
                        return;
                    }

                    if (activeLeafletMode !== nextMode) {
                        switchLeafletLayer(nextMode);
                    }
                    window.setTimeout(() => map.invalidateSize(), 90);
                });
            };

            modeButtons.forEach((button, buttonIndex) => {
                button.addEventListener('click', () => {
                    const mode = button.dataset.profileMapMode || 'street';
                    activateMode(mode);
                    setMenuOpen(false);
                });

                button.addEventListener('keydown', (event) => {
                    if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') {
                        return;
                    }

                    event.preventDefault();
                    const step = event.key === 'ArrowRight' ? 1 : -1;
                    const nextIndex = (buttonIndex + step + modeButtons.length) % modeButtons.length;
                    const nextButton = modeButtons[nextIndex];
                    nextButton.focus();
                    const mode = nextButton.dataset.profileMapMode || 'street';
                    activateMode(mode);
                });
            });

            activateMode('street');
            mapSurface.dataset.mapInitialized = 'true';
        };

        const structureGrids = Array.from(document.querySelectorAll('.profile-structure-grid'));
        structureGrids.forEach((grid) => {
            const cards = Array.from(grid.querySelectorAll(':scope > .profile-structure-card'));
            const hasMultipleCards = cards.length > 1;
            cards.forEach((card, index) => {
                card.style.setProperty('--profile-card-stagger', `${Math.min(index * 90, 360)}ms`);
                card.style.setProperty('--profile-photo-stagger', hasMultipleCards ? `${Math.min(index * 220, 880)}ms` : '0ms');
            });
        });

        const standaloneStructureCards = Array.from(document.querySelectorAll('.profile-structure-group > .profile-structure-card'));
        standaloneStructureCards.forEach((card) => {
            card.style.setProperty('--profile-card-stagger', '0ms');
            card.style.setProperty('--profile-photo-stagger', '0ms');
        });

        const structureGroups = Array.from(document.querySelectorAll('.profile-structure-group'));
        const standaloneStructureGroupGrids = Array.from(document.querySelectorAll('.profile-structure-list > .profile-structure-grid'));

        structureGroups.forEach((group, index) => {
            group.style.setProperty('--profile-sequence-delay', `${Math.min(index * 180, 1800)}ms`);
        });

        standaloneStructureGroupGrids.forEach((grid, index) => {
            grid.style.setProperty('--profile-sequence-delay', `${Math.min((structureGroups.length + index) * 180, 2200)}ms`);
        });

        const readMs = (value, fallback = 0) => {
            const parsed = Number.parseFloat(value);
            return Number.isFinite(parsed) ? parsed : fallback;
        };

        const runStructureSequence = (group) => {
            if (!(group instanceof HTMLElement) || group.dataset.sequenceStarted === '1') {
                return;
            }

            group.dataset.sequenceStarted = '1';
            group.classList.remove('is-seq-badge', 'is-seq-joint', 'is-seq-card', 'is-seq-photo');

            const groupStyle = window.getComputedStyle(group);
            const baseDelay = Math.max(0, readMs(groupStyle.getPropertyValue('--profile-sequence-delay'), 0));

            window.setTimeout(() => group.classList.add('is-seq-badge'), baseDelay + 40);
            window.setTimeout(() => group.classList.add('is-seq-joint'), baseDelay + 160);
            window.setTimeout(() => group.classList.add('is-seq-card'), baseDelay + 300);
            window.setTimeout(() => group.classList.add('is-seq-photo'), baseDelay + 440);
        };

        const structureShells = Array.from(document.querySelectorAll('.profile-structure-shell'));
        initProfileMapMode();
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion || !('IntersectionObserver' in window)) {
            targets.forEach((element) => element.classList.add('is-visible'));
            structureGroups.forEach((group) => group.classList.add('is-seq-badge', 'is-seq-joint', 'is-seq-card', 'is-seq-photo'));
            structureShells.forEach((shell) => shell.classList.add('is-structure-active'));
            return;
        }

        const revealTarget = (entry, revealObserver) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            if (entry.target.classList.contains('profile-structure-group')) {
                runStructureSequence(entry.target);
            }
            const structureShell = entry.target.classList.contains('profile-structure-shell')
                ? entry.target
                : entry.target.closest('.profile-structure-shell');
            if (structureShell) {
                structureShell.classList.add('is-structure-active');
            }
            revealObserver.unobserve(entry.target);
        };

        const regularTargets = targets.filter((element) => !element.closest('.profile-structure-section'));
        const structureTargets = targets.filter((element) => element.closest('.profile-structure-section'));

        const observer = new IntersectionObserver(
            (entries, revealObserver) => {
                entries.forEach((entry) => revealTarget(entry, revealObserver));
            },
            {
                root: null,
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.18,
            }
        );

        const structureObserver = new IntersectionObserver(
            (entries, revealObserver) => {
                entries.forEach((entry) => revealTarget(entry, revealObserver));
            },
            {
                root: null,
                rootMargin: '-40px 0px -40% 0px',
                threshold: 0,
            }
        );

        regularTargets.forEach((element) => {
            observer.observe(element);
        });

        structureTargets.forEach((element) => {
            structureObserver.observe(element);
        });
    })();
</script>

@endsection
