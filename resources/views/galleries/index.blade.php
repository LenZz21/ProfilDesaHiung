@extends('layouts.app')

@section('content')
@php
    $profileRecord = $profile ?? \App\Models\VillageProfile::query()->first();
    $profileName = trim((string) data_get($profileRecord, 'name', __('Kampung Hiung')));
    $villageName = preg_replace('/^(desa|kampung)\s+/i', '', $profileName) ?: $profileName;
    $albumsOnPage = $galleries->getCollection();
    $photoCountOnPage = $albumsOnPage->sum('items_count');
    $latestAlbum = $albumsOnPage->first();
    $defaultGalleriesHeroTitle = __('Galeri Kampung :village', ['village' => $villageName]);
    $defaultGalleriesHeroSubtitle = __('Dokumentasi kegiatan, pembangunan, dan momen penting masyarakat kampung.');
    $defaultGalleriesHeroImage = 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=1920&q=80';
    $configuredGalleriesHeroTitle = trim((string) data_get($galleriesPageSetting ?? [], 'title', ''));
    $configuredGalleriesHeroSubtitle = trim((string) data_get($galleriesPageSetting ?? [], 'subtitle', ''));
    $configuredGalleriesHeroImage = trim((string) data_get($galleriesPageSetting ?? [], 'hero_image_url', ''));
    $galleriesHeroTitle = $configuredGalleriesHeroTitle !== '' ? $configuredGalleriesHeroTitle : $defaultGalleriesHeroTitle;
    $galleriesHeroSubtitle = $configuredGalleriesHeroSubtitle !== '' ? $configuredGalleriesHeroSubtitle : $defaultGalleriesHeroSubtitle;
    $galleriesHeroImage = $configuredGalleriesHeroImage !== '' ? $configuredGalleriesHeroImage : $defaultGalleriesHeroImage;
@endphp

<style>
    .gallery-page [data-gallery-reveal] {
        opacity: 0;
        transform: translate3d(0, 18px, 0);
        will-change: opacity, transform;
        transition:
            opacity .84s cubic-bezier(.22, 1, .36, 1),
            transform .84s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--gallery-reveal-delay, 0ms);
    }

    .gallery-page [data-gallery-reveal].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .gallery-hero-content > * {
        opacity: 0;
        transform: translate3d(0, 30px, 0);
        animation: gallery-hero-rise .9s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .gallery-hero-content > h1 {
        animation-delay: 120ms;
    }

    .gallery-hero-content > p {
        animation-delay: 260ms;
    }

    .gallery-shimmer {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .gallery-shimmer::after {
        content: '';
        position: absolute;
        inset: -130% -90%;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0) 42%,
            rgba(255, 255, 255, 0.45) 50%,
            rgba(255, 255, 255, 0) 58%
        );
        transform: translate3d(-58%, -58%, 0);
        opacity: 0;
        pointer-events: none;
        z-index: 5;
    }

    @media (hover: hover) and (pointer: fine) {
        .gallery-shimmer::after {
            animation: gallery-shimmer-sweep 5.8s ease-in-out infinite;
        }
    }

    @keyframes gallery-shimmer-sweep {
        0% {
            transform: translate3d(-62%, -62%, 0);
            opacity: 0;
        }

        14% {
            opacity: 1;
        }

        30% {
            transform: translate3d(56%, 56%, 0);
            opacity: 0;
        }

        100% {
            transform: translate3d(56%, 56%, 0);
            opacity: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .gallery-page [data-gallery-reveal] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .gallery-hero-content > * {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }

        .gallery-shimmer::after {
            animation: none;
            opacity: 0;
        }
    }

    @keyframes gallery-hero-rise {
        from {
            opacity: 0;
            transform: translate3d(0, 30px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
</style>

<div class="gallery-page">
<section
    class="relative h-[340px] overflow-hidden bg-scroll bg-cover bg-center bg-no-repeat text-white sm:h-[430px] sm:bg-fixed"
    style="background-image: url('{{ $galleriesHeroImage }}');"
>
    <div class="absolute inset-0 bg-slate-950/55"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/25 to-black/60"></div>

    <div class="gallery-hero-content relative mx-auto flex h-full max-w-7xl items-center justify-center px-4 pb-8 pt-20 text-center sm:px-6 lg:px-8">
        <div>
            <h1 class="text-3xl font-black sm:text-6xl">{{ $galleriesHeroTitle }}</h1>
            <p class="mx-auto mt-4 max-w-3xl text-sm text-slate-100 sm:text-2xl">
                {{ $galleriesHeroSubtitle }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#f0f0f1] pb-16 pt-10 sm:pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-gradient-to-br from-white via-slate-50 to-blue-50/40 p-5 shadow-[0_20px_45px_rgba(15,23,42,0.08)] sm:p-8" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: 80ms;">
            <div class="grid grid-cols-3 gap-2 border-b border-slate-200/80 pb-6 sm:gap-4">
                <div class="gallery-stat-card min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm sm:p-4" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: 120ms;">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">{{ __('Total Album') }}</p>
                    <p class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">{{ $galleries->total() }}</p>
                </div>
                <div class="gallery-stat-card min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm sm:p-4" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: 170ms;">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">{{ __('Foto (halaman ini)') }}</p>
                    <p class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">{{ $photoCountOnPage }}</p>
                </div>
                <div class="gallery-stat-card min-w-0 rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm sm:p-4" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: 220ms;">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">{{ __('Album Terbaru') }}</p>
                    <p class="mt-1 line-clamp-2 text-[11px] font-bold leading-4 text-slate-800 sm:text-sm sm:leading-5">{{ $latestAlbum?->title ?: __('Belum tersedia') }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($galleries as $gallery)
                    <article class="gallery-album-card group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: {{ 140 + ($loop->index * 95) }}ms;">
                        <a href="{{ route('galleries.show', $gallery) }}" class="block">
                            <div class="gallery-shimmer h-52">
                                <img
                                    src="{{ $gallery->cover ? Storage::url($gallery->cover) : 'https://placehold.co/760x460' }}"
                                    alt="{{ $gallery->title }}"
                                    class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                                >
                            </div>
                            <div class="p-5">
                                <p class="text-xs font-semibold text-slate-500">
                                    {{ __(':count foto', ['count' => $gallery->items_count]) }}
                                </p>
                                <h2 class="mt-2 line-clamp-2 text-xl font-black leading-tight text-slate-900 transition-colors group-hover:text-blue-700 sm:text-2xl">
                                    {{ $gallery->title }}
                                </h2>
                                <p class="mt-3 line-clamp-3 text-sm leading-7 text-slate-600 sm:text-base">
                                    {{ \Illuminate\Support\Str::limit($gallery->description ?: __('Dokumentasi kegiatan resmi kampung.'), 120) }}
                                </p>
                                <span class="mt-4 inline-flex text-sm font-semibold text-blue-600 group-hover:text-blue-700 sm:text-base">
                                    {{ __('Lihat Album') }} &#8594;
                                </span>
                            </div>
                        </a>
                    </article>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500" data-gallery-reveal data-gallery-card style="--gallery-reveal-delay: 120ms;">
                        {{ __('Belum ada album galeri yang dipublikasikan.') }}
                    </div>
                @endforelse
            </div>

            @if ($galleries->hasPages())
                <div class="pt-8" data-gallery-reveal style="--gallery-reveal-delay: 220ms;">
                    <div class="flex flex-wrap justify-center gap-2">
                        @if ($galleries->onFirstPage())
                            <span class="inline-flex h-9 items-center rounded-lg bg-slate-200 px-3 text-sm font-semibold text-slate-500">{{ __('Sebelumnya') }}</span>
                        @else
                            <a href="{{ $galleries->previousPageUrl() }}" class="inline-flex h-9 items-center rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
                                {{ __('Sebelumnya') }}
                            </a>
                        @endif

                        @foreach ($galleries->getUrlRange(1, $galleries->lastPage()) as $page => $url)
                            <a
                                href="{{ $url }}"
                                class="grid h-9 w-9 place-items-center rounded-lg text-sm font-semibold transition {{ $galleries->currentPage() === $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}"
                            >
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($galleries->hasMorePages())
                            <a href="{{ $galleries->nextPageUrl() }}" class="inline-flex h-9 items-center rounded-lg bg-white px-3 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">
                                {{ __('Berikutnya') }}
                            </a>
                        @else
                            <span class="inline-flex h-9 items-center rounded-lg bg-slate-200 px-3 text-sm font-semibold text-slate-500">{{ __('Berikutnya') }}</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
<script>
    (() => {
        const revealTargets = Array.from(document.querySelectorAll('[data-gallery-reveal]'));
        if (revealTargets.length === 0) {
            return;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const supportsObserver = 'IntersectionObserver' in window;

        const revealElement = (element) => {
            if (!element.classList.contains('is-visible')) {
                element.classList.add('is-visible');
            }
        };

        const revealVisibleElements = () => {
            revealTargets.forEach((element) => {
                const rect = element.getBoundingClientRect();
                if (rect.top <= window.innerHeight * 0.95) {
                    revealElement(element);
                }
            });
        };

        if (prefersReducedMotion) {
            revealTargets.forEach(revealElement);
            return;
        }

        if (supportsObserver) {
            const observer = new IntersectionObserver((entries, currentObserver) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    revealElement(entry.target);
                    currentObserver.unobserve(entry.target);
                });
            }, {
                threshold: 0.08,
                rootMargin: '0px 0px -6% 0px',
            });

            revealTargets.forEach((element) => {
                observer.observe(element);
            });
        }

        const fallbackHandler = () => revealVisibleElements();
        revealVisibleElements();
        window.addEventListener('scroll', fallbackHandler, { passive: true });
        window.addEventListener('resize', fallbackHandler, { passive: true });
    })();
</script>
</div>
@endsection
