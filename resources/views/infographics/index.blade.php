@extends('layouts.app')

@section('content')
@php
    $pageTitle = $pageTitle ?? ($infographic?->title ?: __('Infografis Penduduk'));
    $pageSubtitle = $pageSubtitle ?? ($infographic?->subtitle ?: __('Statistik kependudukan kampung yang terintegrasi dan transparan.'));
    $thousandsSeparator = app()->getLocale() === 'id' ? '.' : ',';
    $decimalSeparator = app()->getLocale() === 'id' ? ',' : '.';
    $displayChartSections = collect($chartSections)
        ->map(function ($section) {
            $items = collect(data_get($section, 'items', []))
                ->map(fn ($item) => [
                    ...$item,
                    'label' => (string) data_get($item, 'label', '-'),
                ])
                ->values()
                ->all();

            return [
                ...$section,
                'title' => (string) data_get($section, 'title', __('Grafik')),
                'items' => $items,
            ];
        })
        ->values()
        ->all();
@endphp

<style>
    [data-inf-reveal] {
        --inf-reveal-delay: 0ms;
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        transition:
            opacity 700ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 700ms cubic-bezier(0.22, 1, 0.36, 1);
        transition-delay: var(--inf-reveal-delay);
        will-change: opacity, transform;
    }

    [data-inf-reveal]:not(.is-visible) {
        opacity: 0;
        transform: translate3d(0, 26px, 0) scale(0.985);
    }

    [data-inf-chart-card] canvas {
        opacity: 0;
        transform: translate3d(0, 14px, 0) scale(0.992);
        filter: saturate(88%);
        transition:
            opacity 620ms cubic-bezier(0.22, 1, 0.36, 1),
            transform 620ms cubic-bezier(0.22, 1, 0.36, 1),
            filter 620ms ease;
        transition-delay: 80ms;
        will-change: opacity, transform, filter;
    }

    [data-inf-chart-card].is-chart-visible canvas {
        opacity: 1;
        transform: translate3d(0, 0, 0) scale(1);
        filter: none;
    }

    @media (prefers-reduced-motion: reduce) {
        [data-inf-reveal],
        [data-inf-chart-card] canvas {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
            filter: none !important;
        }
    }
</style>

<div class="infographics-page">
<section
    class="infographics-hero relative h-[320px] overflow-hidden bg-fixed bg-cover bg-center bg-no-repeat text-white sm:h-[360px]"
    style="background-image: url('{{ $heroImage }}');"
>
    <div class="absolute inset-0 bg-slate-900/45"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/20 to-black/45"></div>

    <div class="relative mx-auto flex h-full max-w-7xl items-center justify-center px-4 pb-6 pt-24 text-center sm:px-6 lg:px-8">
        <div>
            <h1 class="text-3xl font-black text-white sm:text-4xl">{{ $pageTitle }}</h1>
            <p class="mt-2 text-sm text-slate-100 sm:text-base">{{ $pageSubtitle }}</p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-slate-100 pb-14 pt-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-black text-slate-900">{{ __('Statistik Umum') }}</h2>
            <span class="mt-3 inline-block h-1.5 w-20 rounded bg-blue-600"></span>
        </div>

        <div class="infographics-summary-grid mt-7 grid gap-3" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
            @foreach ($summaryStats as $item)
                @php
                    $value = (float) data_get($item, 'value', 0);
                    $label = (string) data_get($item, 'label', '-');
                    $color = (string) data_get($item, 'color', '#2563eb');
                    $revealDelay = min($loop->index * 60, 260);
                @endphp
                <article
                    data-inf-reveal
                    class="infographics-stat-card rounded-xl border border-slate-200 bg-white px-4 py-4 text-center shadow-sm"
                    style="--inf-reveal-delay: {{ $revealDelay }}ms;"
                >
                    <p class="text-2xl font-black" style="color: {{ $color }}">{{ number_format($value, 0, $decimalSeparator, $thousandsSeparator) }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</p>
                </article>
            @endforeach
        </div>

        <div class="mt-8 space-y-7">
            @foreach ($displayChartSections as $index => $section)
                @php
                    $chartId = 'inf-chart-' . $index;
                    $sectionTitle = (string) data_get($section, 'title', __('Grafik'));
                    $sectionSlug = \Illuminate\Support\Str::slug($sectionTitle ?: 'grafik-' . $index);
                    $chartRevealDelay = min(90 + ($index * 70), 420);
                @endphp

                <article
                    data-inf-reveal
                    data-inf-chart-card
                    data-chart-id="{{ $chartId }}"
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5"
                    style="--inf-reveal-delay: {{ $chartRevealDelay }}ms;"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 sm:text-base">{{ $sectionTitle }}</h3>
                            <span class="mt-2 inline-block h-1 w-12 rounded bg-yellow-400"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                data-chart-export="png"
                                data-chart-id="{{ $chartId }}"
                                data-chart-file="{{ $sectionSlug }}"
                                class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                            >
                                PNG
                            </button>
                            <button
                                type="button"
                                data-chart-export="csv"
                                data-chart-id="{{ $chartId }}"
                                data-chart-file="{{ $sectionSlug }}"
                                class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                            >
                                CSV
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 h-[320px]">
                        <canvas id="{{ $chartId }}"></canvas>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    (() => {
        const sections = @json($displayChartSections);
        const palette = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#84cc16'];
        const charts = {};
        const csvDelimiter = @json(app()->getLocale() === 'id' ? ';' : ',');
        const csvDecimalSeparator = @json($decimalSeparator);

        const chartPlugins = {
            legend: {
                display: true,
                position: 'bottom',
                labels: {
                    boxWidth: 11,
                    boxHeight: 11,
                    usePointStyle: true,
                    pointStyle: 'circle',
                    color: '#334155',
                    font: { size: 11, weight: '600' },
                },
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                titleColor: '#f8fafc',
                bodyColor: '#e2e8f0',
                padding: 10,
            },
        };

        const chartDefinitions = {};

        sections.forEach((section, index) => {
            const chartId = `inf-chart-${index}`;
            const canvas = document.getElementById(chartId);
            const items = Array.isArray(section?.items) ? section.items : [];

            if (!canvas || !items.length) {
                return;
            }

            chartDefinitions[chartId] = { section, items, canvas };
        });

        const createChart = (chartId) => {
            if (charts[chartId]) {
                return charts[chartId];
            }

            const definition = chartDefinitions[chartId];

            if (!definition) {
                return null;
            }

            const { section, items, canvas } = definition;
            const labels = items.map((item) => item?.label ?? '-');
            const values = items.map((item) => Number(item?.value ?? 0));
            const colors = items.map((item, itemIndex) => item?.color || palette[itemIndex % palette.length]);
            const type = ['bar', 'line', 'pie', 'doughnut', 'radar', 'polarArea'].includes(section?.type) ? section.type : 'bar';

            const baseOptions = {
                maintainAspectRatio: false,
                responsive: true,
                plugins: chartPlugins,
                animation: {
                    duration: 950,
                    easing: 'easeOutCubic',
                },
            };

            const isRoundChart = ['pie', 'doughnut', 'polarArea'].includes(type);

            let dataset = {
                label: section?.title || @json(__('Data')),
                data: values,
                borderWidth: 1.4,
            };

            if (type === 'line' || type === 'radar') {
                dataset = {
                    ...dataset,
                    borderColor: colors[0],
                    backgroundColor: `${colors[0]}33`,
                    pointBackgroundColor: colors[0],
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 1.5,
                    pointRadius: 4,
                    fill: type === 'line',
                    tension: 0.32,
                };
            } else if (isRoundChart) {
                dataset = {
                    ...dataset,
                    backgroundColor: colors,
                    borderColor: '#ffffff',
                };
            } else {
                dataset = {
                    ...dataset,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderRadius: 8,
                };
            }

            charts[chartId] = new Chart(canvas, {
                type,
                data: {
                    labels,
                    datasets: [dataset],
                },
                options: {
                    ...baseOptions,
                    scales: isRoundChart || type === 'radar' ? undefined : {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: '#475569',
                                font: { size: 11, weight: '600' },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.2)' },
                            ticks: {
                                precision: 0,
                                color: '#64748b',
                                font: { size: 11 },
                            },
                        },
                    },
                },
            });

            return charts[chartId];
        };

        const download = (fileName, url) => {
            const link = document.createElement('a');
            link.href = url;
            link.download = fileName;
            document.body.appendChild(link);
            link.click();
            link.remove();
        };

        const escapeCsvField = (value) => {
            const normalized = String(value ?? '')
                .replaceAll('"', '""')
                .replace(/\r?\n/g, ' ');

            return `"${normalized}"`;
        };

        const formatCsvNumber = (value) => {
            const numeric = Number(value);

            if (!Number.isFinite(numeric)) {
                return '0';
            }

            if (Number.isInteger(numeric)) {
                return String(numeric);
            }

            return String(numeric).replace('.', csvDecimalSeparator);
        };

        const exportPng = (chartId, fileSlug) => {
            const chart = createChart(chartId);

            if (!chart) {
                return;
            }

            const dataUrl = chart.toBase64Image('image/png', 1);
            download(`${fileSlug}.png`, dataUrl);
        };

        const exportCsv = (chartId, fileSlug) => {
            const chart = createChart(chartId);

            if (!chart) {
                return;
            }

            const labels = chart.data.labels ?? [];
            const dataset = chart.data.datasets?.[0]?.data ?? [];
            const rows = [];

            if (csvDelimiter === ';') {
                rows.push('sep=;');
            }

            rows.push([
                escapeCsvField(@json(__('Label'))),
                escapeCsvField(@json(__('Nilai'))),
            ].join(csvDelimiter));

            labels.forEach((label, index) => {
                rows.push([
                    escapeCsvField(label),
                    formatCsvNumber(dataset[index] ?? 0),
                ].join(csvDelimiter));
            });

            const csv = '\uFEFF' + rows.join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            download(`${fileSlug}.csv`, url);
            setTimeout(() => URL.revokeObjectURL(url), 1000);
        };

        const revealTargets = Array.from(document.querySelectorAll('[data-inf-reveal]'));
        const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        const parseTimeValue = (value) => {
            const normalized = String(value ?? '').trim();

            if (!normalized) {
                return 0;
            }

            if (normalized.endsWith('ms')) {
                return Number.parseFloat(normalized) || 0;
            }

            if (normalized.endsWith('s')) {
                return (Number.parseFloat(normalized) || 0) * 1000;
            }

            return Number.parseFloat(normalized) || 0;
        };

        const getMaxTransitionTotalMs = (element) => {
            const styles = window.getComputedStyle(element);
            const durations = styles.transitionDuration.split(',').map(parseTimeValue);
            const delays = styles.transitionDelay.split(',').map(parseTimeValue);
            const transitionCount = Math.max(durations.length, delays.length, 1);

            let maxTotal = 0;

            for (let index = 0; index < transitionCount; index += 1) {
                const duration = durations[index] ?? durations[durations.length - 1] ?? 0;
                const delay = delays[index] ?? delays[delays.length - 1] ?? 0;
                const total = duration + delay;

                if (total > maxTotal) {
                    maxTotal = total;
                }
            }

            return maxTotal;
        };

        const startChartAfterCardReveal = (element) => {
            if (!element?.matches?.('[data-inf-chart-card]') || element.dataset.chartSequenceStarted === '1') {
                return;
            }

            element.dataset.chartSequenceStarted = '1';

            const revealDuration = getMaxTransitionTotalMs(element);
            const waitBeforeChartStage = revealDuration + 20;

            window.setTimeout(() => {
                element.classList.add('is-chart-visible');

                window.setTimeout(() => {
                    const chartId = element.getAttribute('data-chart-id');

                    if (chartId) {
                        createChart(chartId);
                    }
                }, 90);
            }, waitBeforeChartStage);
        };

        const revealAll = () => {
            revealTargets.forEach((element) => {
                element.classList.add('is-visible');

                if (element.matches('[data-inf-chart-card]')) {
                    element.classList.add('is-chart-visible');
                }
            });

            Object.keys(chartDefinitions).forEach((chartId) => createChart(chartId));
        };

        if (reduceMotionQuery.matches || !('IntersectionObserver' in window)) {
            revealAll();
        } else {
            const revealObserver = new IntersectionObserver(
                (entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        const element = entry.target;
                        element.classList.add('is-visible');
                        startChartAfterCardReveal(element);

                        observer.unobserve(element);
                    });
                },
                {
                    threshold: 0.18,
                    rootMargin: '0px 0px -8% 0px',
                }
            );

            revealTargets.forEach((element) => revealObserver.observe(element));
        }

        document.querySelectorAll('[data-chart-export]').forEach((button) => {
            button.addEventListener('click', () => {
                const type = button.dataset.chartExport;
                const chartId = button.dataset.chartId;
                const fileSlug = button.dataset.chartFile || @json(__('infografis'));

                if (type === 'png') {
                    exportPng(chartId, fileSlug);
                    return;
                }

                exportCsv(chartId, fileSlug);
            });
        });
    })();
</script>
@endsection
