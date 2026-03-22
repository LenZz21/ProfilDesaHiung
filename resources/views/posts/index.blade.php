@extends('layouts.app')

@section('content')
@php
    $villageName = $profile?->name ?? __('Desa');
    $thousandsSeparator = app()->getLocale() === 'id' ? '.' : ',';
    $decimalSeparator = app()->getLocale() === 'id' ? ',' : '.';
    $defaultPostsHeroTitle = __('Berita Kampung :village', ['village' => $villageName]);
    $defaultPostsHeroSubtitle = __('Kumpulan informasi dan kegiatan terbaru di Kampung :village.', ['village' => $villageName]);
    $defaultPostsHeroImage = 'https://images.unsplash.com/photo-1495020689067-958852a7765e?auto=format&fit=crop&w=1920&q=80';
    $configuredPostsHeroTitle = trim((string) data_get($postsPageSetting ?? [], 'title', ''));
    $configuredPostsHeroSubtitle = trim((string) data_get($postsPageSetting ?? [], 'subtitle', ''));
    $configuredPostsHeroImage = trim((string) data_get($postsPageSetting ?? [], 'hero_image_url', ''));
    $postsHeroTitle = $configuredPostsHeroTitle !== '' ? $configuredPostsHeroTitle : $defaultPostsHeroTitle;
    $postsHeroSubtitle = $configuredPostsHeroSubtitle !== '' ? $configuredPostsHeroSubtitle : $defaultPostsHeroSubtitle;
    $postsHeroImage = $configuredPostsHeroImage !== '' ? $configuredPostsHeroImage : $defaultPostsHeroImage;
@endphp

<style>
    .posts-arrive {
        opacity: 1;
        transform: none;
        transition: none;
    }

    .js-posts-arrive .posts-arrive {
        opacity: 0;
        transform: translateY(16px);
        transition:
            opacity var(--posts-arrive-duration, 1220ms) cubic-bezier(0.22, 1, 0.36, 1),
            transform var(--posts-arrive-duration, 1220ms) cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--posts-arrive-delay, 0ms);
        will-change: opacity, transform;
    }

    .js-posts-arrive .posts-arrive[data-posts-arrive='hero'] {
        transform: translateY(12px);
        --posts-arrive-duration: 1380ms;
    }

    .js-posts-arrive .posts-arrive.is-visible {
        opacity: 1;
        transform: translateY(0);
        will-change: auto;
    }

    .news-shimmer {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .news-shimmer::after {
        content: '';
        position: absolute;
        inset: -130% -90%;
        background: linear-gradient(
            135deg,
            rgba(255, 255, 255, 0) 42%,
            rgba(255, 255, 255, 0.5) 50%,
            rgba(255, 255, 255, 0) 58%
        );
        transform: translate3d(-58%, -58%, 0);
        animation: news-shimmer-sweep 5.8s ease-in-out infinite;
        pointer-events: none;
        z-index: 5;
    }

    .news-shimmer--compact::after {
        animation-duration: 6.6s;
        animation-delay: 0.8s;
    }

    @keyframes news-shimmer-sweep {
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
        .posts-arrive {
            opacity: 1;
            transform: none;
            transition: none;
            will-change: auto;
        }

        .news-shimmer::after {
            animation: none;
            opacity: 0;
        }
    }
</style>

<script>
    document.documentElement.classList.add('js-posts-arrive');
</script>

<div class="posts-page">
<section
    class="posts-hero relative h-[340px] overflow-hidden bg-fixed bg-cover bg-center bg-no-repeat text-white sm:h-[430px]"
    style="background-image: url('{{ $postsHeroImage }}');"
>
    <div class="absolute inset-0 bg-slate-900/52"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/25 to-black/60"></div>

    <div class="relative mx-auto flex h-full max-w-7xl items-center justify-center px-4 pb-8 pt-24 text-center sm:px-6 lg:px-8">
        <div>
            <h1 class="posts-arrive text-3xl font-black sm:text-6xl" data-posts-arrive="hero" style="--posts-arrive-delay: 80ms;">{{ $postsHeroTitle }}</h1>
            <p class="posts-arrive mx-auto mt-4 max-w-3xl text-sm text-slate-100 sm:text-2xl" data-posts-arrive="hero" style="--posts-arrive-delay: 200ms;">
                {{ $postsHeroSubtitle }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#efeff1] pb-16 pt-12 sm:pb-20">
    <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
        @if ($headlinePost)
            <article class="posts-headline-card posts-arrive overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_14px_34px_rgba(15,23,42,0.12)]" data-posts-arrive style="--posts-arrive-delay: 160ms;">
                <div class="grid lg:grid-cols-2">
                    <div class="news-shimmer h-80 lg:h-full">
                        <img
                            src="{{ $headlinePost->thumbnail ? Storage::url($headlinePost->thumbnail) : 'https://placehold.co/980x700' }}"
                            alt="{{ $headlinePost->title }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                    <div class="flex flex-col justify-center p-6 sm:p-8">
                        <p class="text-xs font-semibold text-slate-500">
                            {{ $headlinePost->category ?: __('Umum') }} &#8226; {{ $headlinePost->published_at?->translatedFormat('d F Y') }}
                        </p>
                        <a href="{{ route('posts.show', $headlinePost) }}" class="mt-2 text-xl font-black leading-tight text-slate-900 transition-colors hover:text-blue-700 sm:text-3xl">
                            {{ $headlinePost->title }}
                        </a>
                        <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-lg sm:leading-8">
                            {{ $headlinePost->excerpt ?: \Illuminate\Support\Str::limit(strip_tags((string) $headlinePost->content), 190) }}
                        </p>
                        <a href="{{ route('posts.show', $headlinePost) }}" class="mt-4 inline-flex text-sm font-semibold text-blue-600 hover:text-blue-700 sm:text-base">
                            {{ __('Baca Selengkapnya') }} &#8594;
                        </a>
                    </div>
                </div>
            </article>
        @endif

        <div class="posts-arrive space-y-4 border-b border-slate-300 pb-6" data-posts-arrive style="--posts-arrive-delay: 240ms;">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('posts.index', array_filter(['q' => $searchQuery])) }}"
                        class="inline-flex rounded-full border px-4 py-1.5 text-sm font-semibold transition {{ $activeCategory === '' ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-blue-300 hover:text-blue-700' }}"
                    >
                        {{ __('Semua') }}
                    </a>
                    @foreach ($categories as $category)
                        <a
                            href="{{ route('posts.index', array_filter(['category' => $category, 'q' => $searchQuery])) }}"
                            class="inline-flex rounded-full border px-4 py-1.5 text-sm font-semibold transition {{ $activeCategory === $category ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-blue-300 hover:text-blue-700' }}"
                        >
                            {{ $category }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" class="w-full lg:w-[340px]">
                    @if ($activeCategory !== '')
                        <input type="hidden" name="category" value="{{ $activeCategory }}">
                    @endif
                    <input
                        type="text"
                        name="q"
                        value="{{ $searchQuery }}"
                        placeholder="{{ __('Cari berita...') }}"
                        class="w-full rounded-full border border-slate-300 bg-white px-5 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                </form>
            </div>

            @if ($activeCategory !== '' || $searchQuery !== '')
                <p class="text-sm text-slate-500">
                    {{ __('Menampilkan') }} {{ number_format($totalMatched, 0, $decimalSeparator, $thousandsSeparator) }} {{ __('berita') }}
                    @if ($activeCategory !== '')
                        {{ __('kategori') }} <span class="font-semibold text-slate-700">{{ $activeCategory }}</span>
                    @endif
                    @if ($searchQuery !== '')
                        {{ __('dengan kata kunci') }} <span class="font-semibold text-slate-700">"{{ $searchQuery }}"</span>
                    @endif
                    @if ($latestPublishedAt)
                        ({{ __('update terakhir') }} {{ $latestPublishedAt->translatedFormat('d M Y') }}).
                    @endif
                </p>
            @endif
        </div>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($posts as $post)
                <article class="posts-list-card posts-arrive overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-[0_14px_32px_rgba(37,99,235,0.16)]" data-posts-arrive style="--posts-arrive-delay: {{ min(($loop->index * 95) + 280, 980) }}ms;">
                    <div class="news-shimmer news-shimmer--compact h-52">
                        <img
                            src="{{ $post->thumbnail ? Storage::url($post->thumbnail) : 'https://placehold.co/760x460' }}"
                            alt="{{ $post->title }}"
                            class="h-full w-full object-cover"
                        >
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-semibold text-slate-500">
                            {{ $post->category ?: __('Umum') }} &#8226; {{ $post->published_at?->translatedFormat('d F Y') }}
                        </p>
                        <a href="{{ route('posts.show', $post) }}" class="mt-2 block text-xl font-black leading-tight text-slate-900 hover:text-blue-700 sm:text-2xl">
                            {{ $post->title }}
                        </a>
                        <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">
                            {{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags((string) $post->content), 118) }}
                        </p>
                        <a href="{{ route('posts.show', $post) }}" class="mt-4 inline-flex text-xl font-semibold text-blue-600 hover:text-blue-700 sm:text-2xl">
                            {{ __('Baca Selengkapnya') }} &#8594;
                        </a>
                    </div>
                </article>
            @empty
                <div class="posts-arrive col-span-full rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500" data-posts-arrive style="--posts-arrive-delay: 260ms;">
                    {{ __('Berita tidak ditemukan. Coba ubah kategori atau kata kunci pencarian.') }}
                </div>
            @endforelse
        </div>

        @if ($posts->hasPages())
            <div class="posts-arrive pt-4" data-posts-arrive style="--posts-arrive-delay: 320ms;">
                <div class="flex justify-center gap-2">
                    @foreach ($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)
                        <a
                            href="{{ $url }}"
                            class="grid h-9 w-9 place-items-center rounded-lg text-sm font-semibold transition {{ $posts->currentPage() === $page ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' }}"
                        >
                            {{ $page }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>

</div>
<script>
    (() => {
        const arriveElements = Array.from(document.querySelectorAll('[data-posts-arrive]'));
        if (arriveElements.length === 0) {
            return;
        }

        const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
            arriveElements.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const revealQueue = new Set();
        let revealFrameId = null;
        const flushRevealQueue = () => {
            revealFrameId = null;
            revealQueue.forEach((element) => element.classList.add('is-visible'));
            revealQueue.clear();
        };

        const observer = new IntersectionObserver(
            (entries, currentObserver) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    revealQueue.add(entry.target);
                    if (revealFrameId === null) {
                        revealFrameId = window.requestAnimationFrame(flushRevealQueue);
                    }
                    currentObserver.unobserve(entry.target);
                });
            },
            {
                root: null,
                rootMargin: '0px 0px -5% 0px',
                threshold: 0.05,
            }
        );

        arriveElements.forEach((element) => {
            observer.observe(element);
        });
    })();
</script>
@endsection
