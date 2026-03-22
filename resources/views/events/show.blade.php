@extends('layouts.app')

@section('content')
@php
    $villageName = $profile?->name ?? __('Desa');
    $statusKey = $event->agendaStatus();
    $statusMeta = match ($statusKey) {
        \App\Models\Event::AGENDA_STATUS_ONGOING => ['label' => __('Berlangsung'), 'class' => 'bg-emerald-600/95 text-white'],
        \App\Models\Event::AGENDA_STATUS_FINISHED => ['label' => __('Selesai'), 'class' => 'bg-rose-700/95 text-white'],
        default => ['label' => __('Mendatang'), 'class' => 'bg-indigo-600/95 text-white'],
    };
@endphp

<style>
    .event-detail-page {
        --event-ease: cubic-bezier(0.22, 1, 0.36, 1);
    }

    .event-hero-badge,
    .event-hero-title,
    .event-hero-lead {
        opacity: 0;
        transform: translate3d(0, 18px, 0);
        animation: event-hero-rise 880ms var(--event-ease) forwards;
    }

    .event-hero-badge {
        animation-delay: 100ms;
    }

    .event-hero-title {
        animation-delay: 220ms;
    }

    .event-hero-lead {
        animation-delay: 360ms;
    }

    [data-event-reveal] {
        opacity: 0;
        transform: translate3d(0, 24px, 0);
        transition:
            opacity 840ms var(--event-ease),
            transform 920ms var(--event-ease);
        transition-delay: var(--event-reveal-delay, 0ms);
        will-change: opacity, transform;
    }

    [data-event-reveal='left'] {
        transform: translate3d(-20px, 24px, 0);
    }

    [data-event-reveal='right'] {
        transform: translate3d(20px, 24px, 0);
    }

    [data-event-reveal].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    @keyframes event-hero-rise {
        from {
            opacity: 0;
            transform: translate3d(0, 18px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .event-hero-badge,
        .event-hero-title,
        .event-hero-lead,
        [data-event-reveal] {
            animation: none !important;
            transition: none !important;
        }

        [data-event-reveal] {
            opacity: 1;
            transform: none;
        }
    }
</style>

<div class="event-detail-page">
<section
    class="relative h-[340px] overflow-hidden bg-cover bg-center bg-no-repeat text-white sm:h-[400px]"
    style="background-image: url('{{ $event->banner ? Storage::url($event->banner) : 'https://placehold.co/1600x900' }}');"
>
    <div class="absolute inset-0 bg-slate-900/60"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/20 to-black/60"></div>

    <div class="relative mx-auto flex h-full max-w-6xl items-end px-4 pb-10 sm:px-6 lg:px-8">
        <div class="max-w-4xl">
            <span class="event-hero-badge inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['class'] }}">
                {{ $statusMeta['label'] }}
            </span>
            <h1 class="event-hero-title mt-4 text-3xl font-black leading-tight sm:text-5xl">{{ $event->title }}</h1>
            <p class="event-hero-lead mt-3 text-sm text-slate-100 sm:text-base">
                {{ __('Agenda resmi :village pada :date.', ['village' => $villageName, 'date' => $event->start_at?->translatedFormat('d F Y')]) }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#f4f6f8] py-12 sm:py-16">
    <div class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-[1.45fr_0.55fr] lg:px-8">
        <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_12px_32px_rgba(15,23,42,0.09)] sm:p-8" data-event-reveal="left" style="--event-reveal-delay: 40ms;">
            <h2 class="text-3xl font-black text-slate-900">{{ __('Deskripsi Agenda') }}</h2>
            <span class="mt-3 inline-block h-1.5 w-20 rounded bg-blue-600"></span>

            <div class="mt-6 space-y-4 leading-8 text-slate-700">
                {!! filled($event->description) ? $event->description : '<p>' . __('Deskripsi agenda belum tersedia.') . '</p>' !!}
            </div>
        </article>

        <aside class="space-y-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.08)]" data-event-reveal="right" style="--event-reveal-delay: 130ms;">
                <h3 class="text-base font-black text-slate-900">{{ __('Informasi Agenda') }}</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Tanggal Mulai') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $event->start_at?->translatedFormat('d M Y, H:i') }} WIB</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Tanggal Selesai') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $event->end_at?->translatedFormat('d M Y, H:i') ? $event->end_at->translatedFormat('d M Y, H:i') . ' WIB' : __('Belum ditentukan') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Lokasi') }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $event->location ?: __('Belum diisi') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Status') }}</p>
                        <p class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.08)]" data-event-reveal="right" style="--event-reveal-delay: 220ms;">
                <h3 class="text-base font-black text-slate-900">{{ __('Navigasi') }}</h3>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('events.index') }}" class="inline-flex w-full justify-center rounded-full border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white">
                        {{ __('Kembali ke Agenda') }}
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex w-full justify-center rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                        {{ __('Ke Beranda') }}
                    </a>
                </div>
            </article>
        </aside>
    </div>
</section>

@if ($relatedEvents->isNotEmpty())
    <section class="bg-[#f4f6f8] pb-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="text-center" data-event-reveal="card" style="--event-reveal-delay: 60ms;">
                <h2 class="text-3xl font-black text-slate-900">{{ __('Agenda Lainnya') }}</h2>
                <span class="mt-3 inline-block h-1.5 w-20 rounded bg-blue-600"></span>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ($relatedEvents as $related)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_10px_28px_rgba(15,23,42,0.08)] transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_16px_42px_rgba(37,99,235,0.18)]" data-event-reveal="card" style="--event-reveal-delay: {{ 120 + ($loop->index * 90) }}ms;">
                        <img src="{{ $related->banner ? Storage::url($related->banner) : 'https://placehold.co/700x420' }}" alt="{{ $related->title }}" class="h-44 w-full object-cover">
                        <div class="p-4">
                            <p class="text-xs font-semibold text-blue-700">{{ $related->start_at?->translatedFormat('d M Y H:i') }} WIB</p>
                            <a href="{{ route('events.show', $related) }}" class="mt-2 block text-sm font-bold text-slate-900 hover:text-blue-700">
                                {{ $related->title }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
</div>

<script>
    (() => {
        const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        const initEventReveal = () => {
            const revealTargets = Array.from(document.querySelectorAll('[data-event-reveal]'));
            if (revealTargets.length === 0) {
                return;
            }

            if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
                revealTargets.forEach((element) => element.classList.add('is-visible'));
                return;
            }

            const observer = new IntersectionObserver(
                (entries, revealObserver) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    });
                },
                {
                    root: null,
                    rootMargin: '0px 0px -12% 0px',
                    threshold: 0.12,
                }
            );

            revealTargets.forEach((element, index) => {
                if (!element.style.getPropertyValue('--event-reveal-delay')) {
                    element.style.setProperty('--event-reveal-delay', `${Math.min(index * 80, 520)}ms`);
                }

                observer.observe(element);
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initEventReveal, { once: true });
        } else {
            initEventReveal();
        }
    })();
</script>
@endsection
