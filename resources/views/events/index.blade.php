@extends('layouts.app')

@section('content')
@php
    $villageName = $profile?->name ?? __('Desa');
    $thousandsSeparator = app()->getLocale() === 'id' ? '.' : ',';
    $decimalSeparator = app()->getLocale() === 'id' ? ',' : '.';
    $defaultEventsHeroTitle = __('Agenda :village', ['village' => $villageName]);
    $defaultEventsHeroSubtitle = __('Informasi jadwal kegiatan kampung yang sedang berjalan dan akan datang.');
    $defaultEventsHeroImage = 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1920&q=80';
    $configuredEventsHeroTitle = trim((string) data_get($eventsPageSetting ?? [], 'title', ''));
    $configuredEventsHeroSubtitle = trim((string) data_get($eventsPageSetting ?? [], 'subtitle', ''));
    $configuredEventsHeroImage = trim((string) data_get($eventsPageSetting ?? [], 'hero_image_url', ''));
    $eventsHeroTitle = $configuredEventsHeroTitle !== '' ? $configuredEventsHeroTitle : $defaultEventsHeroTitle;
    $eventsHeroSubtitle = $configuredEventsHeroSubtitle !== '' ? $configuredEventsHeroSubtitle : $defaultEventsHeroSubtitle;
    $eventsHeroImage = $configuredEventsHeroImage !== '' ? $configuredEventsHeroImage : $defaultEventsHeroImage;
@endphp

<style>
    .events-page [data-events-reveal] {
        opacity: 0;
        transform: translate3d(0, 26px, 0) scale(0.985);
        transition:
            opacity 820ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 960ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--events-reveal-delay, 0ms);
        will-change: opacity, transform;
    }

    .events-page [data-events-reveal].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
    }

    .events-hero {
        background-attachment: fixed;
    }

    .events-hero::before {
        content: '';
        position: absolute;
        inset: -28% -30%;
        background:
            radial-gradient(circle at 18% 18%, rgba(56, 189, 248, 0.36), rgba(56, 189, 248, 0) 52%),
            radial-gradient(circle at 78% 15%, rgba(99, 102, 241, 0.26), rgba(99, 102, 241, 0) 48%);
        animation: events-hero-glow 12s ease-in-out infinite alternate;
        pointer-events: none;
    }

    .events-hero-content > * {
        opacity: 0;
        transform: translate3d(0, 16px, 0);
        animation: events-hero-rise 920ms cubic-bezier(0.22, 1, 0.36, 1) forwards;
    }

    .events-hero-content > h1 {
        animation-delay: 180ms;
    }

    .events-hero-content > p {
        animation-delay: 420ms;
    }

    .events-content-section {
        position: relative;
        overflow: hidden;
        isolation: isolate;
    }

    .events-content-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 8% 14%, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0) 36%),
            radial-gradient(circle at 88% 28%, rgba(14, 165, 233, 0.09), rgba(14, 165, 233, 0) 38%);
        pointer-events: none;
        z-index: 0;
    }

    .events-content-section > .mx-auto {
        position: relative;
        z-index: 1;
    }

    .events-stat-card {
        position: relative;
        overflow: hidden;
        transition: transform 380ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 360ms ease, border-color 320ms ease;
    }

    .events-stat-card::after {
        content: '';
        position: absolute;
        inset: auto -38% -68% -38%;
        height: 70%;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0));
        opacity: 0.72;
        transform: translate3d(0, 12px, 0);
        transition: transform 520ms cubic-bezier(0.22, 1, 0.36, 1), opacity 420ms ease;
        pointer-events: none;
    }

    .events-stat-card:hover {
        transform: translate3d(0, -4px, 0);
        border-color: rgba(59, 130, 246, 0.42);
        box-shadow: 0 22px 40px rgba(30, 64, 175, 0.16);
    }

    .events-stat-card:hover::after {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .events-agenda-title-accent {
        transform-origin: center;
        transform: scaleX(0.3);
        opacity: 0.66;
        transition: transform 820ms cubic-bezier(0.22, 1, 0.36, 1), opacity 560ms ease;
        transition-delay: 180ms;
    }

    .events-agenda-heading.is-visible .events-agenda-title-accent {
        transform: scaleX(1);
        opacity: 1;
    }

    .events-agenda-card {
        transform: translate3d(0, 0, 0) scale(1);
        transform-origin: center;
        transition: transform 420ms cubic-bezier(0.22, 1, 0.36, 1), box-shadow 340ms ease, border-color 320ms ease;
        will-change: transform;
    }

    .events-agenda-card:hover {
        transform: translate3d(0, -4px, 0) scale(1.025);
        border-color: rgba(59, 130, 246, 0.42);
        box-shadow: 0 24px 48px rgba(30, 64, 175, 0.2);
    }

    /* Override reveal transform state so hover scale can take effect */
    .events-page .events-agenda-card[data-events-reveal].is-visible:hover {
        transform: translate3d(0, -4px, 0) scale(1.025);
    }

    @keyframes events-hero-rise {
        from {
            opacity: 0;
            transform: translate3d(0, 16px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @keyframes events-hero-glow {
        from {
            transform: translate3d(-1%, 0, 0) scale(1);
            filter: blur(0);
        }

        to {
            transform: translate3d(1.5%, -1.5%, 0) scale(1.04);
            filter: blur(4px);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .events-page [data-events-reveal] {
            opacity: 1;
            transform: none;
            transition: none;
        }

        .events-hero::before,
        .events-hero-content > *,
        .events-stat-card,
        .events-agenda-title-accent,
        .events-agenda-card {
            animation: none !important;
            transition: none !important;
            transform: none !important;
        }
    }

    @media (max-width: 1023px) {
        .events-hero {
            background-attachment: scroll;
        }
    }

    :root[data-theme='dark'] .events-content-section::before {
        background:
            radial-gradient(circle at 8% 14%, rgba(56, 189, 248, 0.16), rgba(56, 189, 248, 0) 42%),
            radial-gradient(circle at 88% 28%, rgba(99, 102, 241, 0.12), rgba(99, 102, 241, 0) 44%);
    }

    :root[data-theme='dark'] .events-stat-card {
        border-color: rgba(100, 161, 226, 0.34) !important;
        box-shadow: 0 16px 34px rgba(2, 6, 23, 0.4) !important;
    }

    :root[data-theme='dark'] .events-stat-card:hover {
        border-color: rgba(125, 211, 252, 0.6) !important;
        box-shadow: 0 24px 46px rgba(2, 6, 23, 0.56), 0 8px 24px rgba(37, 99, 235, 0.3) !important;
    }

    :root[data-theme='dark'] .events-agenda-card {
        border-color: rgba(100, 161, 226, 0.34) !important;
        box-shadow: 0 16px 34px rgba(2, 6, 23, 0.42) !important;
    }

    :root[data-theme='dark'] .events-agenda-card:hover {
        border-color: rgba(125, 211, 252, 0.62) !important;
        box-shadow: 0 24px 46px rgba(2, 6, 23, 0.56), 0 8px 24px rgba(37, 99, 235, 0.3) !important;
    }
</style>

<div class="events-page">
<section
    class="events-hero relative h-[320px] overflow-hidden bg-cover bg-center bg-no-repeat text-white sm:h-[360px]"
    style="background-image: url('{{ $eventsHeroImage }}');"
>
    <div class="absolute inset-0 bg-slate-900/55"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-black/55"></div>

    <div class="relative mx-auto flex h-full max-w-7xl items-center justify-center px-4 pb-8 pt-24 text-center sm:px-6 lg:px-8">
        <div class="events-hero-content">
            <h1 class="text-3xl font-black sm:text-5xl">{{ $eventsHeroTitle }}</h1>
            <p class="mx-auto mt-3 max-w-3xl text-sm text-slate-100 sm:text-lg">
                {{ $eventsHeroSubtitle }}
            </p>
        </div>
    </div>
</section>

<section class="events-content-section section-soft-separator bg-[#f4f6f8] pb-16 pt-12 sm:pb-20">
    <div class="mx-auto max-w-7xl space-y-10 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-3 gap-3 sm:gap-4">
            <article class="events-stat-card rounded-2xl border border-slate-200 bg-white p-3 sm:p-5 shadow-[0_10px_26px_rgba(15,23,42,0.08)]" data-events-reveal style="--events-reveal-delay: 40ms;">
                <p class="text-[10px] font-semibold uppercase tracking-wide leading-tight text-slate-500 sm:text-xs sm:tracking-wider">{{ __('Total Agenda') }}</p>
                <p class="mt-1.5 text-2xl font-black text-blue-700 sm:mt-2 sm:text-3xl" data-events-counter data-counter-target="{{ $totalAgenda }}">{{ number_format($totalAgenda, 0, $decimalSeparator, $thousandsSeparator) }}</p>
            </article>
            <article class="events-stat-card rounded-2xl border border-slate-200 bg-white p-3 sm:p-5 shadow-[0_10px_26px_rgba(15,23,42,0.08)]" data-events-reveal style="--events-reveal-delay: 160ms;">
                <p class="text-[10px] font-semibold uppercase tracking-wide leading-tight text-slate-500 sm:text-xs sm:tracking-wider">{{ __('Agenda Mendatang') }}</p>
                <p class="mt-1.5 text-2xl font-black text-emerald-600 sm:mt-2 sm:text-3xl" data-events-counter data-counter-target="{{ $upcomingAgenda }}">{{ number_format($upcomingAgenda, 0, $decimalSeparator, $thousandsSeparator) }}</p>
            </article>
            <article class="events-stat-card rounded-2xl border border-slate-200 bg-white p-3 sm:p-5 shadow-[0_10px_26px_rgba(15,23,42,0.08)]" data-events-reveal style="--events-reveal-delay: 280ms;">
                <p class="text-[10px] font-semibold uppercase tracking-wide leading-tight text-slate-500 sm:text-xs sm:tracking-wider">{{ __('Titik Lokasi') }}</p>
                <p class="mt-1.5 text-2xl font-black text-indigo-600 sm:mt-2 sm:text-3xl" data-events-counter data-counter-target="{{ $locationCount }}">{{ number_format($locationCount, 0, $decimalSeparator, $thousandsSeparator) }}</p>
            </article>
        </div>

        <div class="events-agenda-heading text-center" data-events-reveal>
            <h2 class="text-4xl font-black text-slate-900">{{ __('Daftar Agenda') }}</h2>
            <span class="events-agenda-title-accent mt-3 inline-block h-1.5 w-20 rounded bg-blue-600"></span>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($events as $event)
                @php
                    $statusKey = $event->agendaStatus();
                    $statusMeta = match ($statusKey) {
                        \App\Models\Event::AGENDA_STATUS_ONGOING => ['label' => __('Berlangsung'), 'class' => 'bg-emerald-600/95 text-white'],
                        \App\Models\Event::AGENDA_STATUS_FINISHED => ['label' => __('Selesai'), 'class' => 'bg-rose-700/95 text-white'],
                        default => ['label' => __('Mendatang'), 'class' => 'bg-indigo-600/95 text-white'],
                    };
                @endphp

                <article class="events-agenda-card group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_12px_32px_rgba(15,23,42,0.10)]" data-events-reveal style="--events-reveal-delay: {{ min($loop->index * 90, 700) }}ms;">
                    <div class="relative overflow-hidden">
                        <img
                            src="{{ $event->banner ? Storage::url($event->banner) : 'https://placehold.co/720x420' }}"
                            alt="{{ $event->title }}"
                            class="h-48 w-full object-cover"
                        >
                        <span class="absolute left-3 top-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusMeta['class'] }}">
                            {{ $statusMeta['label'] }}
                        </span>
                    </div>

                    <div class="p-5">
                        <p class="text-xs font-semibold tracking-wide text-blue-700">
                            {{ $event->start_at?->translatedFormat('d M Y') }} &#8226; {{ $event->start_at?->translatedFormat('H:i') }} WIB
                        </p>
                        <a href="{{ route('events.show', $event) }}" class="mt-2 block text-lg font-black leading-snug text-slate-900 transition-colors hover:text-blue-700">
                            {{ $event->title }}
                        </a>
                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            {{ \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 110) }}
                        </p>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <p class="text-xs font-medium text-slate-500">
                                {{ $event->location ?: __('Lokasi belum diisi') }}
                            </p>
                            <a href="{{ route('events.show', $event) }}" class="inline-flex rounded-full border border-blue-600 px-4 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white">
                                {{ __('Detail') }}
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-500" data-events-reveal>
                    {{ __('Belum ada agenda yang dipublikasikan.') }}
                </div>
            @endforelse
        </div>

        @if ($events->hasPages())
            <div class="pt-2">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</section>
</div>

<script>
    (() => {
        const page = document.querySelector('.events-page');
        if (!(page instanceof HTMLElement)) {
            return;
        }

        const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        const locale = document.documentElement.lang || 'id-ID';
        const formatter = new Intl.NumberFormat(locale, { maximumFractionDigits: 0 });
        const revealTargets = Array.from(page.querySelectorAll('[data-events-reveal]'));
        const counterTargets = Array.from(page.querySelectorAll('[data-events-counter]'));

        const revealElement = (element) => {
            element.classList.add('is-visible');
        };

        const runCounter = (element, animate = true) => {
            if (element.dataset.counterDone === 'true') {
                return;
            }

            const targetValue = Number.parseInt(element.dataset.counterTarget || '0', 10);
            if (!Number.isFinite(targetValue) || targetValue < 0) {
                element.dataset.counterDone = 'true';
                return;
            }

            if (!animate) {
                element.textContent = formatter.format(targetValue);
                element.dataset.counterDone = 'true';
                return;
            }

            const duration = 1100;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.round(targetValue * eased);
                element.textContent = formatter.format(currentValue);

                if (progress < 1) {
                    window.requestAnimationFrame(tick);
                    return;
                }

                element.textContent = formatter.format(targetValue);
                element.dataset.counterDone = 'true';
            };

            window.requestAnimationFrame(tick);
        };

        if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
            revealTargets.forEach(revealElement);
            counterTargets.forEach((element) => runCounter(element, !reduceMotionQuery.matches));
        } else {
            const revealObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        revealElement(entry.target);
                        observer.unobserve(entry.target);
                    });
                },
                {
                    root: null,
                    rootMargin: '0px 0px -12% 0px',
                    threshold: 0.08,
                }
            );

            revealTargets.forEach((element) => {
                revealObserver.observe(element);
            });

            const counterObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        runCounter(entry.target);
                        observer.unobserve(entry.target);
                    });
                },
                {
                    root: null,
                    rootMargin: '0px 0px -18% 0px',
                    threshold: 0.22,
                }
            );

            counterTargets.forEach((element) => {
                counterObserver.observe(element);
            });
        }

    })();
</script>
@endsection
