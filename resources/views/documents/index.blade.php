@extends('layouts.app')

@section('content')
@php
    $documentsHeroBackground = collect([
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
        ?? 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1920&q=80';
    $defaultDocumentsHeroTitle = __('Publikasi Dokumen');
    $defaultDocumentsHeroSubtitle = __('Dokumen publikasi dan informasi resmi desa.');
    $configuredDocumentsHeroTitle = trim((string) data_get($documentsPageSetting ?? [], 'title', ''));
    $configuredDocumentsHeroSubtitle = trim((string) data_get($documentsPageSetting ?? [], 'subtitle', ''));
    $configuredDocumentsHeroImage = trim((string) data_get($documentsPageSetting ?? [], 'hero_image_url', ''));
    $documentsHeroTitle = $configuredDocumentsHeroTitle !== '' ? $configuredDocumentsHeroTitle : $defaultDocumentsHeroTitle;
    $documentsHeroSubtitle = $configuredDocumentsHeroSubtitle !== '' ? $configuredDocumentsHeroSubtitle : $defaultDocumentsHeroSubtitle;
    $documentsHeroBackground = $configuredDocumentsHeroImage !== '' ? $configuredDocumentsHeroImage : $documentsHeroBackground;
@endphp

<style>
    .documents-page [data-document-reveal] {
        opacity: 0;
        transform: translate3d(0, 16px, 0);
        transition:
            opacity .68s cubic-bezier(.22, 1, .36, 1),
            transform .68s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--document-reveal-delay, 0ms);
    }

    .documents-page [data-document-reveal].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .documents-hero-content > * {
        opacity: 0;
        transform: translate3d(0, 18px, 0);
        animation: documents-hero-rise .9s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .documents-hero-content > h1 {
        animation-delay: 110ms;
    }

    .documents-hero-content > p {
        animation-delay: 250ms;
    }

    .documents-filter-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%23334155' stroke-width='2'%3E%3Cpath d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 0.95rem;
    }

    .documents-table-shell {
        border: 1px solid rgba(148, 163, 184, 0.3);
    }

    .documents-download-btn {
        background: linear-gradient(135deg, #14b8a6, #0ea5a5);
        box-shadow: 0 10px 18px rgba(13, 148, 136, 0.28);
    }

    .documents-download-btn:hover {
        background: linear-gradient(135deg, #2dd4bf, #14b8a6);
    }

    :root[data-theme='dark'] .documents-filter-select {
        color: #d5e4ff;
        border-color: rgba(96, 165, 250, 0.25);
        background-color: rgba(9, 31, 63, 0.56);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none' stroke='%239fbbe4' stroke-width='2'%3E%3Cpath d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    }

    :root[data-theme='dark'] .documents-table-shell {
        border-color: rgba(88, 145, 210, 0.34);
        background: linear-gradient(180deg, rgba(8, 30, 59, 0.72), rgba(10, 36, 67, 0.64));
        box-shadow: 0 30px 50px rgba(2, 6, 23, 0.45), inset 0 1px 0 rgba(186, 230, 253, 0.08);
    }

    :root[data-theme='dark'] .documents-header {
        color: #f8fafc;
        border-color: rgba(88, 145, 210, 0.34);
        background: rgba(30, 41, 59, 0.48);
    }

    :root[data-theme='dark'] .documents-row {
        color: #e2e8f0;
        border-color: rgba(88, 145, 210, 0.3);
    }

    :root[data-theme='dark'] .documents-row-muted {
        color: #bfd6f7;
    }

    .documents-pagination nav > div:first-child p,
    .documents-pagination nav svg {
        color: #334155 !important;
    }

    .documents-pagination nav a,
    .documents-pagination nav span {
        border-color: rgba(148, 163, 184, 0.42) !important;
    }

    .documents-pagination nav a {
        background: rgba(255, 255, 255, 0.86) !important;
        color: #0f172a !important;
    }

    .documents-pagination nav span[aria-current='page'] span {
        background: rgba(37, 99, 235, 0.18) !important;
        color: #1d4ed8 !important;
    }

    :root[data-theme='dark'] .documents-pagination nav > div:first-child p,
    :root[data-theme='dark'] .documents-pagination nav svg {
        color: #d2e5ff !important;
    }

    :root[data-theme='dark'] .documents-pagination nav a,
    :root[data-theme='dark'] .documents-pagination nav span {
        border-color: rgba(88, 145, 210, 0.34) !important;
    }

    :root[data-theme='dark'] .documents-pagination nav a {
        background: rgba(8, 30, 59, 0.54) !important;
        color: #d6e7ff !important;
    }

    :root[data-theme='dark'] .documents-pagination nav span[aria-current='page'] span {
        background: rgba(37, 99, 235, 0.45) !important;
        color: #ffffff !important;
    }

    @media (max-width: 767.98px) {
        .documents-page.has-mobile-filter-enhanced .documents-filter-native {
            display: none;
        }

        .documents-toolbar {
            align-items: stretch;
            gap: .85rem;
            margin-bottom: 1.05rem;
        }

        .documents-toolbar h2 {
            font-size: 1.35rem;
            line-height: 1.25;
        }

        .documents-filter-form {
            width: 100%;
        }

        .documents-filter-select {
            min-width: 0;
            width: 100%;
            border-radius: .85rem;
            padding: .72rem 2.35rem .72rem .95rem;
            background-position: right .8rem center;
        }

        .documents-filter-mobile {
            width: 100%;
        }

        .documents-filter-trigger {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border-radius: .85rem;
            border: 1px solid rgba(59, 130, 246, 0.34);
            background: linear-gradient(180deg, #f8fbff, #eaf3ff);
            color: #1e3a5f;
            padding: .72rem .9rem;
            font-size: .95rem;
            font-weight: 700;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8), 0 8px 18px rgba(59, 130, 246, 0.15);
            transition: border-color .26s ease, box-shadow .26s ease, background-color .26s ease;
        }

        .documents-filter-trigger:hover {
            border-color: rgba(37, 99, 235, 0.46);
            background: linear-gradient(180deg, #f2f8ff, #e3efff);
        }

        .documents-filter-trigger svg {
            flex-shrink: 0;
            transition: transform .46s cubic-bezier(.16, 1, .3, 1);
        }

        .documents-filter-mobile.is-open .documents-filter-trigger svg {
            transform: rotate(180deg);
        }

        .documents-filter-trigger:focus-visible {
            outline: none;
            border-color: rgba(37, 99, 235, 0.62);
            box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.22), 0 8px 18px rgba(59, 130, 246, 0.16);
        }

        .documents-filter-panel {
            display: grid;
            grid-template-rows: 0fr;
            margin-top: 0;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            transform: translate3d(0, -10px, 0) scale(.985);
            transform-origin: top center;
            transition:
                grid-template-rows .54s cubic-bezier(.16, 1, .3, 1),
                margin-top .54s cubic-bezier(.16, 1, .3, 1),
                opacity .34s ease,
                transform .54s cubic-bezier(.16, 1, .3, 1);
        }

        .documents-filter-panel-inner {
            border-radius: .9rem;
            border: 1px solid rgba(96, 165, 250, 0.34);
            background: linear-gradient(180deg, #ffffff, #f3f8ff);
            box-shadow: 0 16px 28px rgba(15, 23, 42, 0.18);
            min-height: 0;
            padding: 0 .3rem;
            max-height: min(46vh, 310px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            transition: padding .54s cubic-bezier(.16, 1, .3, 1);
        }

        .documents-filter-mobile.is-open .documents-filter-panel {
            grid-template-rows: 1fr;
            margin-top: .45rem;
            opacity: 1;
            pointer-events: auto;
            transform: translate3d(0, 0, 0) scale(1);
        }

        .documents-filter-mobile.is-open .documents-filter-panel-inner {
            padding: .3rem;
        }

        .documents-filter-option {
            display: block;
            width: 100%;
            border: 0;
            border-radius: .68rem;
            background: transparent;
            color: #1e3a5f;
            text-align: left;
            font-size: 1rem;
            font-weight: 600;
            padding: .56rem .66rem;
            opacity: 0;
            transform: translate3d(0, 8px, 0);
            transition: background-color .22s ease, color .22s ease;
        }

        .documents-filter-option + .documents-filter-option {
            margin-top: .15rem;
        }

        .documents-filter-mobile.is-open .documents-filter-option {
            animation: documents-filter-option-in .42s cubic-bezier(.16, 1, .3, 1) both;
            animation-delay: calc(var(--doc-filter-option-index, 0) * 42ms);
        }

        .documents-filter-option:hover,
        .documents-filter-option:focus-visible {
            outline: none;
            background: rgba(59, 130, 246, 0.12);
            color: #0f172a;
        }

        .documents-filter-option.is-active {
            background: rgba(37, 99, 235, 0.2);
            color: #0f172a;
            box-shadow: inset 0 0 0 1px rgba(96, 165, 250, 0.42);
        }

        .documents-table-shell {
            border-radius: 1.1rem;
            padding: .42rem;
        }

        .documents-table-shell .overflow-x-auto {
            overflow: visible;
        }

        .documents-table,
        .documents-table-body,
        .documents-row-item,
        .documents-cell {
            display: block;
            width: 100%;
        }

        .documents-table-head {
            display: none;
        }

        .documents-row-item {
            margin-bottom: .62rem;
            border: 1px solid rgba(148, 163, 184, 0.34);
            border-radius: .95rem;
            background: rgba(248, 250, 252, 0.96);
            padding: .2rem .82rem .82rem;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
        }

        .documents-row-item:last-child {
            margin-bottom: 0;
        }

        .documents-row-item.documents-row-empty {
            padding: 0;
        }

        .documents-row.documents-row-item {
            border-bottom: 1px solid rgba(148, 163, 184, 0.34) !important;
        }

        .documents-empty-cell {
            padding: 1.1rem .9rem !important;
        }

        .documents-cell {
            border-bottom: 1px dashed rgba(148, 163, 184, 0.44);
            padding: .52rem 0 !important;
            text-align: left !important;
        }

        .documents-cell:last-child {
            border-bottom: 0;
            padding-top: .72rem !important;
        }

        .documents-cell::before {
            content: attr(data-label);
            display: block;
            margin-bottom: .18rem;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #475569;
        }

        .documents-cell-title {
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.38;
            color: #0f172a;
            padding-top: .7rem !important;
        }

        .documents-download-btn {
            min-width: 0;
            width: 100%;
            border-radius: .75rem;
            padding: .62rem .95rem;
            font-size: .94rem;
            box-shadow: 0 8px 16px rgba(13, 148, 136, 0.24);
        }

        .documents-pagination {
            margin-top: 1rem;
        }

        :root[data-theme='dark'] .documents-row-item {
            border-color: rgba(88, 145, 210, 0.34);
            background: rgba(8, 30, 59, 0.58);
            box-shadow: 0 12px 24px rgba(2, 6, 23, 0.34);
        }

        :root[data-theme='dark'] .documents-cell {
            border-color: rgba(88, 145, 210, 0.36);
        }

        :root[data-theme='dark'] .documents-cell::before {
            color: #a9c6ed;
        }

        :root[data-theme='dark'] .documents-cell-title {
            color: #e2efff;
        }

        :root[data-theme='dark'] .documents-filter-trigger {
            border-color: rgba(125, 211, 252, 0.45);
            background: linear-gradient(180deg, rgba(7, 29, 58, 0.96), rgba(6, 24, 48, 0.96));
            color: #e5f0ff;
            box-shadow: inset 0 1px 0 rgba(186, 230, 253, 0.12), 0 10px 20px rgba(2, 6, 23, 0.34);
        }

        :root[data-theme='dark'] .documents-filter-trigger:hover {
            border-color: rgba(125, 211, 252, 0.62);
            background: linear-gradient(180deg, rgba(9, 36, 72, 0.96), rgba(8, 29, 56, 0.96));
        }

        :root[data-theme='dark'] .documents-filter-panel-inner {
            border-color: rgba(125, 211, 252, 0.44);
            background: linear-gradient(180deg, rgba(4, 22, 45, 0.98), rgba(3, 17, 35, 0.98));
            box-shadow: 0 18px 30px rgba(2, 6, 23, 0.46);
        }

        :root[data-theme='dark'] .documents-filter-option {
            color: #cfe3ff;
        }

        :root[data-theme='dark'] .documents-filter-option:hover,
        :root[data-theme='dark'] .documents-filter-option:focus-visible {
            background: rgba(56, 189, 248, 0.2);
            color: #ffffff;
        }

        :root[data-theme='dark'] .documents-filter-option.is-active {
            background: rgba(37, 99, 235, 0.5);
            color: #ffffff;
            box-shadow: inset 0 0 0 1px rgba(147, 197, 253, 0.56);
        }
    }

    @keyframes documents-filter-option-in {
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
        .documents-page [data-document-reveal] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .documents-hero-content > * {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }

        .documents-filter-trigger,
        .documents-filter-trigger svg,
        .documents-filter-panel,
        .documents-filter-panel-inner,
        .documents-filter-option {
            transition: none !important;
            animation: none !important;
        }

        .documents-filter-mobile.is-open .documents-filter-option {
            opacity: 1 !important;
            transform: none !important;
        }
    }

    @keyframes documents-hero-rise {
        from {
            opacity: 0;
            transform: translate3d(0, 18px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
</style>

<div class="documents-page">
<section
    class="relative h-[300px] overflow-hidden bg-scroll bg-cover bg-center bg-no-repeat text-white sm:h-[360px] sm:bg-fixed"
    style="background-image: url('{{ $documentsHeroBackground }}');"
>
    <div class="absolute inset-0 bg-slate-950/45"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/55 via-slate-900/35 to-emerald-950/55"></div>
    <div class="absolute inset-0">
        <div class="documents-hero-content mx-auto flex h-full max-w-6xl items-center justify-center px-4 text-center sm:px-6">
            <div>
                <h1 class="text-3xl font-black sm:text-5xl">{{ $documentsHeroTitle }}</h1>
                <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-100 sm:text-lg">
                    {{ $documentsHeroSubtitle }}
                </p>
            </div>
        </div>
    </div>
</section>

<section class="documents-section section-soft-separator bg-[#efeff1] pb-16 pt-10 sm:pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="documents-toolbar mb-7 flex flex-wrap items-center justify-between gap-4 sm:mb-8" data-document-reveal style="--document-reveal-delay: 60ms;">
            <h2 class="text-2xl font-black text-slate-900 sm:text-3xl">{{ __('Publikasi Dokumen') }}</h2>
            <form method="GET" class="documents-filter-form w-full sm:w-auto">
                <label for="document-category" class="sr-only">{{ __('Kategori') }}</label>
                <select
                    id="document-category"
                    name="category"
                    class="documents-filter-select documents-filter-native min-w-[210px] w-full rounded-full border border-slate-300 bg-white px-5 py-2.5 pr-10 text-sm font-semibold text-slate-700 transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-200 sm:w-auto"
                    onchange="this.form.submit()"
                    data-doc-filter-native
                >
                    <option value="">{{ __('Semua Kategori') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected($activeCategory === $category)>{{ $category }}</option>
                    @endforeach
                </select>

                <div class="documents-filter-mobile md:hidden" data-doc-filter-mobile hidden>
                    <button
                        type="button"
                        data-doc-filter-trigger
                        aria-expanded="false"
                        aria-controls="documents-mobile-category-panel"
                        class="documents-filter-trigger"
                    >
                        <span>{{ $activeCategory !== '' ? $activeCategory : __('Semua Kategori') }}</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.1" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7"/>
                        </svg>
                    </button>

                    <div id="documents-mobile-category-panel" class="documents-filter-panel" data-doc-filter-panel>
                        <div class="documents-filter-panel-inner">
                            <button
                                type="submit"
                                name="category"
                                value=""
                                data-doc-filter-option
                                style="--doc-filter-option-index: 0;"
                                class="documents-filter-option {{ $activeCategory === '' ? 'is-active' : '' }}"
                            >
                                {{ __('Semua Kategori') }}
                            </button>
                            @foreach ($categories as $category)
                                <button
                                    type="submit"
                                    name="category"
                                    value="{{ $category }}"
                                    data-doc-filter-option
                                    style="--doc-filter-option-index: {{ $loop->iteration }};"
                                    class="documents-filter-option {{ $activeCategory === $category ? 'is-active' : '' }}"
                                >
                                    {{ $category }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="documents-table-shell overflow-hidden rounded-3xl bg-white shadow-[0_20px_40px_rgba(15,23,42,0.12)]" data-document-reveal style="--document-reveal-delay: 130ms;">
            <div class="overflow-x-auto">
                <table class="documents-table min-w-full text-base text-slate-800">
                    <thead class="documents-header documents-table-head border-b border-slate-200 bg-slate-100/95">
                        <tr>
                            <th class="px-6 py-4 text-left text-[1.05rem] font-bold">{{ __('Judul') }}</th>
                            <th class="px-6 py-4 text-left text-[1.05rem] font-bold">{{ __('Kategori') }}</th>
                            <th class="px-6 py-4 text-left text-[1.05rem] font-bold">{{ __('Tanggal') }}</th>
                            <th class="px-6 py-4 text-right text-[1.05rem] font-bold">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="documents-table-body">
                        @forelse ($documents as $document)
                            <tr class="documents-row documents-row-item border-b border-slate-200 last:border-b-0" data-document-reveal style="--document-reveal-delay: {{ min(140 + ($loop->index * 75), 560) }}ms;">
                                <td data-label="{{ __('Judul') }}" class="documents-cell documents-cell-title px-6 py-3.5">{{ $document->title }}</td>
                                <td data-label="{{ __('Kategori') }}" class="documents-cell px-6 py-3.5">{{ $document->category ?: __('Umum') }}</td>
                                <td data-label="{{ __('Tanggal') }}" class="documents-cell px-6 py-3.5">{{ optional($document->published_at)->translatedFormat('d M Y') ?: '-' }}</td>
                                <td data-label="{{ __('Aksi') }}" class="documents-cell px-6 py-3 text-right">
                                    <a
                                        href="{{ route('documents.download', $document) }}"
                                        class="documents-download-btn inline-flex min-w-[138px] items-center justify-center rounded-xl px-4 py-2 text-lg font-bold text-white transition focus:outline-none focus:ring-2 focus:ring-cyan-300"
                                    >
                                        {{ __('Download') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr class="documents-row documents-row-item documents-row-empty" data-document-reveal style="--document-reveal-delay: 120ms;">
                                <td colspan="4" class="documents-row-muted documents-empty-cell px-5 py-8 text-center text-sm">
                                    {{ __('Belum ada dokumen.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="documents-pagination mt-8" data-document-reveal style="--document-reveal-delay: 220ms;">
            {{ $documents->links() }}
        </div>
    </div>
</section>
<script>
    (() => {
        const revealTargets = Array.from(document.querySelectorAll('[data-document-reveal]'));
        if (revealTargets.length === 0) {
            return;
        }

        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const lowPerformanceDevice = window.matchMedia('(pointer: coarse)').matches
            || ((navigator.deviceMemory ?? 8) <= 4);
        if (prefersReducedMotion || lowPerformanceDevice) {
            revealTargets.forEach((element) => element.classList.add('is-visible'));
            return;
        }

        const observer = new IntersectionObserver((entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                currentObserver.unobserve(entry.target);
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -6% 0px',
        });

        revealTargets.forEach((element) => {
            observer.observe(element);
        });
    })();

    (() => {
        const page = document.querySelector('.documents-page');
        if (!page) {
            return;
        }

        const mobileFilters = Array.from(page.querySelectorAll('[data-doc-filter-mobile]'));
        if (mobileFilters.length === 0) {
            return;
        }

        page.classList.add('has-mobile-filter-enhanced');

        const filterConfigs = mobileFilters.map((filter) => ({
            filter,
            nativeSelect: filter.closest('form')?.querySelector('[data-doc-filter-native]') ?? null,
        }));

        const syncNativeSelectState = () => {
            const mobileMode = window.matchMedia('(max-width: 767.98px)').matches;
            filterConfigs.forEach(({ nativeSelect }) => {
                if (nativeSelect instanceof HTMLSelectElement) {
                    nativeSelect.disabled = mobileMode;
                }
            });
        };

        const setFilterOpen = (filter, isOpen) => {
            const trigger = filter.querySelector('[data-doc-filter-trigger]');
            if (!(trigger instanceof HTMLElement)) {
                return;
            }

            filter.classList.toggle('is-open', isOpen);
            trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        const closeAllFilters = (except = null) => {
            mobileFilters.forEach((filter) => {
                if (filter === except) {
                    return;
                }

                setFilterOpen(filter, false);
            });
        };

        mobileFilters.forEach((filter) => {
            filter.hidden = false;

            const trigger = filter.querySelector('[data-doc-filter-trigger]');
            const panel = filter.querySelector('[data-doc-filter-panel]');
            if (!(trigger instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                return;
            }

            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                const nextOpen = !filter.classList.contains('is-open');
                closeAllFilters(nextOpen ? filter : null);
                setFilterOpen(filter, nextOpen);
            });

            panel.querySelectorAll('[data-doc-filter-option]').forEach((option) => {
                option.addEventListener('click', () => {
                    setFilterOpen(filter, false);
                });
            });
        });

        document.addEventListener('click', (event) => {
            if (!(event.target instanceof Node)) {
                return;
            }

            const clickedInsideAnyFilter = mobileFilters.some((filter) => filter.contains(event.target));
            if (!clickedInsideAnyFilter) {
                closeAllFilters();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAllFilters();
            }
        });

        syncNativeSelectState();

        window.addEventListener('resize', () => {
            syncNativeSelectState();
            if (window.matchMedia('(min-width: 768px)').matches) {
                closeAllFilters();
            }
        }, { passive: true });
    })();
</script>
</div>
@endsection
