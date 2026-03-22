@extends('layouts.app')

@section('content')
@php
    $villageName = $profile?->name ?? __('Desa');
    $wordCount = str_word_count(strip_tags((string) $post->content));
    $readingMinutes = max(1, (int) ceil($wordCount / 200));
    $postUrl = url()->current();
    $shareText = trim($post->title . ' - ' . ($post->excerpt ?: __('Baca update terbaru dari :village.', ['village' => $villageName])));
    $encodedPostUrl = rawurlencode($postUrl);
    $encodedShareText = rawurlencode($shareText);
    $shareLinks = [
        'whatsapp' => "https://api.whatsapp.com/send?text={$encodedShareText}%20{$encodedPostUrl}",
        'telegram' => "https://t.me/share/url?url={$encodedPostUrl}&text={$encodedShareText}",
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encodedPostUrl}",
        'twitter' => "https://twitter.com/intent/tweet?url={$encodedPostUrl}&text={$encodedShareText}",
    ];
@endphp

<style>
    .post-detail-reveal {
        opacity: 0;
        transform: translateY(22px);
        transition:
            opacity 760ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 760ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--post-reveal-delay, 0ms);
        will-change: opacity, transform;
    }

    .post-detail-reveal[data-post-detail-reveal='hero'] {
        transform: translateY(18px);
    }

    .post-detail-reveal[data-post-detail-reveal='side'] {
        transform: translate(14px, 18px);
    }

    .post-detail-reveal.is-visible {
        opacity: 1;
        transform: translate(0, 0);
        will-change: auto;
    }

    .post-detail-sidebar-card {
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    @media (prefers-reduced-motion: reduce) {
        .post-detail-reveal {
            opacity: 1;
            transform: none;
            transition: none;
        }
    }
</style>

<section
    class="relative h-[340px] overflow-hidden bg-cover bg-center bg-no-repeat text-white sm:h-[400px]"
    style="background-image: url('{{ $post->thumbnail ? Storage::url($post->thumbnail) : 'https://placehold.co/1600x900' }}');"
>
    <div class="absolute inset-0 bg-slate-900/60"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/20 to-black/60"></div>

    <div class="relative mx-auto flex h-full max-w-6xl items-end px-4 pb-10 sm:px-6 lg:px-8">
        <div class="max-w-4xl">
            @if ($post->category)
                <span class="post-detail-reveal inline-flex rounded-full bg-blue-600/95 px-3 py-1 text-xs font-semibold text-white" data-post-detail-reveal="hero" style="--post-reveal-delay: 40ms;">
                    {{ $post->category }}
                </span>
            @endif
            <h1 class="post-detail-reveal mt-4 text-xl font-black leading-tight sm:text-3xl" data-post-detail-reveal="hero" style="--post-reveal-delay: 120ms;">{{ $post->title }}</h1>
            <p class="post-detail-reveal mt-3 text-[11px] text-slate-100 sm:text-xs" data-post-detail-reveal="hero" style="--post-reveal-delay: 200ms;">
                {{ __('Berita resmi :village dipublikasikan pada :date.', ['village' => $villageName, 'date' => $post->published_at?->translatedFormat('d F Y')]) }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#f4f6f8] py-12 sm:py-16">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.45fr_0.55fr] lg:px-8">
        <article class="post-detail-reveal rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_12px_32px_rgba(15,23,42,0.09)] sm:p-8" data-post-detail-reveal="main" style="--post-reveal-delay: 60ms;">
            <h2 class="text-xl font-black text-slate-900 sm:text-2xl">{{ __('Isi Berita') }}</h2>
            <span class="mt-3 inline-block h-1.5 w-20 rounded bg-blue-600"></span>

            <div class="mt-6 space-y-2.5 text-sm leading-6 text-slate-700 [&_a]:text-blue-700 [&_a]:underline [&_h1]:text-xl [&_h1]:font-black [&_h2]:text-lg [&_h2]:font-black [&_h3]:text-base [&_h3]:font-black [&_img]:rounded-xl [&_img]:shadow-sm [&_ul]:list-disc [&_ul]:pl-6 [&_ol]:list-decimal [&_ol]:pl-6 [&_strong]:font-bold [&_strong]:text-slate-900">
                {!! $post->content !!}
            </div>
        </article>

        <aside class="space-y-4 lg:sticky lg:top-24 lg:self-start">
            <article class="post-detail-reveal post-detail-sidebar-card rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.08)]" data-post-detail-reveal="side" style="--post-reveal-delay: 120ms;">
                <h3 class="text-sm font-black text-slate-900">{{ __('Informasi Berita') }}</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Tanggal Publikasi') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $post->published_at?->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Kategori') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $post->category ?: __('Umum') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Estimasi Baca') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $readingMinutes }} {{ __('menit') }}</p>
                    </div>
                </div>
            </article>

            <article class="post-detail-reveal post-detail-sidebar-card rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.08)]" data-post-detail-reveal="side" style="--post-reveal-delay: 180ms;">
                <h3 class="text-sm font-black text-slate-900">{{ __('Bagikan Berita') }}</h3>
                <p class="mt-2 text-sm text-slate-600">{{ __('Bagikan berita ini ke aplikasi favorit Anda.') }}</p>

                <div class="mt-4 flex items-center gap-3 overflow-x-auto pb-1">
                    <button
                        type="button"
                        data-share-native
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-blue-600 transition hover:-translate-y-0.5 hover:text-blue-700"
                        aria-label="{{ __('Bagikan dari perangkat') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0l-4 4m4-4l4 4M4 14v4a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4"/>
                        </svg>
                    </button>
                    <a
                        href="{{ $shareLinks['telegram'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-sky-500 transition hover:-translate-y-0.5 hover:text-sky-600"
                        aria-label="{{ __('Bagikan ke Telegram') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M21.5 3.3a1 1 0 0 0-1-.13L2.8 10.1a1 1 0 0 0 .09 1.86l4.58 1.65 1.6 5.06a1 1 0 0 0 1.75.3l2.55-3.3 4.36 3.39a1 1 0 0 0 1.58-.55L22 4.2a1 1 0 0 0-.5-.9zM9 13.3l8.62-5.28-6.46 6.76-.93 1.2-.4-1.29-.83-2.22z"/>
                        </svg>
                    </a>
                    <a
                        href="{{ $shareLinks['whatsapp'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-emerald-500 transition hover:-translate-y-0.5 hover:text-emerald-600"
                        aria-label="{{ __('Bagikan ke WhatsApp') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M12 2.1a9.8 9.8 0 0 0-8.47 14.74L2 22l5.34-1.5A9.9 9.9 0 1 0 12 2.1Zm0 17.9a8 8 0 0 1-4.08-1.12l-.3-.18-3.1.86.84-3.02-.2-.31A8 8 0 1 1 12 20Zm4.4-5.83c-.24-.12-1.43-.7-1.65-.78-.22-.08-.38-.12-.54.12-.16.23-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1-.37-1.9-1.17-.7-.62-1.17-1.38-1.3-1.62-.14-.24-.01-.37.1-.48.1-.1.24-.26.36-.38.12-.12.16-.2.24-.34.08-.14.04-.26-.02-.38-.06-.12-.54-1.3-.74-1.78-.2-.49-.4-.42-.54-.43h-.46a.88.88 0 0 0-.64.3c-.22.24-.84.82-.84 2s.86 2.31.98 2.47c.12.16 1.69 2.58 4.09 3.62.57.25 1.02.4 1.37.52.58.18 1.1.15 1.52.09.46-.07 1.43-.58 1.63-1.14.2-.56.2-1.03.14-1.13-.06-.1-.22-.16-.46-.28Z"/>
                        </svg>
                    </a>
                    <a
                        href="{{ $shareLinks['facebook'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-blue-600 transition hover:-translate-y-0.5 hover:text-blue-700"
                        aria-label="{{ __('Bagikan ke Facebook') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.5 22v-8.2h2.77l.42-3.2H13.5V8.56c0-.93.26-1.56 1.6-1.56h1.7V4.13A23.1 23.1 0 0 0 14.33 4C11.9 4 10.2 5.48 10.2 8.2v2.4H7.5v3.2h2.7V22h3.3Z"/>
                        </svg>
                    </a>
                    <a
                        href="{{ $shareLinks['twitter'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-slate-900 transition hover:-translate-y-0.5 hover:text-slate-700"
                        aria-label="{{ __('Bagikan ke Twitter') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="m4 4 6.5 8.7L4.4 20h2.1l5-6 4.5 6h4L13 11.7 18.8 4h-2.1l-4.6 5.5L8 4H4Zm3.2 1.8h1.9l7.7 10.4h-1.9L7.2 5.8Z"/>
                        </svg>
                    </a>
                    <button
                        type="button"
                        data-share-copy
                        data-share-url="{{ $postUrl }}"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center text-slate-500 transition hover:-translate-y-0.5 hover:text-slate-700"
                        aria-label="{{ __('Salin Tautan') }}"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14 8.6 15.4a3 3 0 1 1-4.2-4.2l3.2-3.2a3 3 0 0 1 4.2 0m2.2 1.8 1.4-1.4a3 3 0 1 1 4.2 4.2l-3.2 3.2a3 3 0 0 1-4.2 0M8 12h8"/>
                        </svg>
                    </button>
                </div>
                <p data-share-status class="mt-2 text-xs font-medium text-slate-500" aria-live="polite"></p>
            </article>

            <article class="post-detail-reveal post-detail-sidebar-card rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.08)]" data-post-detail-reveal="side" style="--post-reveal-delay: 240ms;">
                <h3 class="text-sm font-black text-slate-900">{{ __('Navigasi') }}</h3>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('posts.index') }}" class="inline-flex w-full justify-center rounded-full border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white">
                        {{ __('Kembali ke Berita') }}
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex w-full justify-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        {{ __('Ke Beranda') }}
                    </a>
                </div>
            </article>
        </aside>
    </div>
</section>

@if ($relatedPosts->isNotEmpty())
    <section class="bg-[#f4f6f8] pb-16 pt-6 sm:pt-8">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="post-detail-reveal flex items-end justify-between gap-4 border-b border-slate-200 pb-4" data-post-detail-reveal="main" style="--post-reveal-delay: 90ms;">
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-blue-600">{{ __('Lainnya') }}</p>
                    <h2 class="mt-1 text-lg font-black text-slate-900 sm:text-xl">{{ __('Berita Lainnya') }}</h2>
                </div>
                <a
                    href="{{ route('posts.index') }}"
                    class="inline-flex items-center rounded-full border border-slate-300 px-3 py-1.5 text-[11px] font-semibold text-slate-700 transition hover:border-blue-600 hover:text-blue-700"
                >
                    {{ __('Lihat Semua') }}
                </a>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                @foreach ($relatedPosts as $related)
                    <article class="post-detail-reveal group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_8px_24px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_16px_36px_rgba(37,99,235,0.16)]" data-post-detail-reveal="main" style="--post-reveal-delay: {{ min(($loop->index * 90) + 130, 460) }}ms;">
                        <div class="relative overflow-hidden">
                            <img src="{{ $related->thumbnail ? Storage::url($related->thumbnail) : 'https://placehold.co/700x420' }}" alt="{{ $related->title }}" class="h-40 w-full object-cover transition duration-500 group-hover:scale-105">
                            @if ($related->category)
                                <span class="absolute left-3 top-3 rounded-full bg-black/55 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-sm">
                                    {{ $related->category }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <p class="text-[10px] font-semibold text-blue-700 sm:text-[11px]">{{ $related->published_at?->translatedFormat('d M Y') }}</p>
                            <a href="{{ route('posts.show', $related) }}" class="mt-1 block text-[11px] font-bold leading-5 text-slate-900 transition group-hover:text-blue-700 sm:text-xs">
                                {{ str($related->title)->limit(88) }}
                            </a>
                            <p class="mt-2 text-[11px] leading-5 text-slate-600">
                                {{ str(strip_tags((string) ($related->excerpt ?: $related->content)))->limit(92) }}
                            </p>
                            <span class="mt-3 inline-flex text-[11px] font-semibold text-blue-700">
                                {{ __('Baca Berita') }}
                            </span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const revealElements = Array.from(document.querySelectorAll('[data-post-detail-reveal]'));
        const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        if (revealElements.length > 0) {
            if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
                revealElements.forEach((element) => element.classList.add('is-visible'));
            } else {
                const revealObserver = new IntersectionObserver(
                    (entries, observer) => {
                        entries.forEach((entry) => {
                            if (!entry.isIntersecting) {
                                return;
                            }

                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        });
                    },
                    {
                        root: null,
                        rootMargin: '0px 0px -10% 0px',
                        threshold: 0.12,
                    }
                );

                revealElements.forEach((element) => {
                    revealObserver.observe(element);
                });
            }
        }

        const shareUrl = @json($postUrl);
        const shareTitle = @json($post->title);
        const shareText = @json($post->excerpt ?: __('Baca update terbaru dari :village.', ['village' => $villageName]));
        const nativeButton = document.querySelector('[data-share-native]');
        const copyButton = document.querySelector('[data-share-copy]');
        const statusText = document.querySelector('[data-share-status]');

        const setStatus = (message, isError = false) => {
            if (!statusText) return;
            statusText.textContent = message;
            statusText.classList.toggle('text-emerald-600', !isError);
            statusText.classList.toggle('text-rose-600', isError);
            statusText.classList.toggle('text-slate-500', false);
        };

        if (nativeButton) {
            if (!navigator.share) {
                nativeButton.classList.add('hidden');
            } else {
                nativeButton.addEventListener('click', async () => {
                    try {
                        await navigator.share({
                            title: shareTitle,
                            text: shareText,
                            url: shareUrl,
                        });
                    } catch (error) {
                        // Pengguna membatalkan atau browser menolak aksi share.
                    }
                });
            }
        }

        if (copyButton) {
            copyButton.addEventListener('click', async () => {
                const link = copyButton.getAttribute('data-share-url') || shareUrl;
                if (!link) return;

                if (navigator.clipboard && window.isSecureContext) {
                    try {
                        await navigator.clipboard.writeText(link);
                        setStatus(@json(__('Tautan berhasil disalin.')));
                        return;
                    } catch (error) {
                        // Fallback ke metode lama jika clipboard API gagal.
                    }
                }

                const textarea = document.createElement('textarea');
                textarea.value = link;
                textarea.setAttribute('readonly', '');
                textarea.style.position = 'absolute';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();

                const copied = document.execCommand('copy');
                document.body.removeChild(textarea);
                setStatus(copied ? @json(__('Tautan berhasil disalin.')) : @json(__('Gagal menyalin tautan.')), !copied);
            });
        }
    });
</script>
@endsection
