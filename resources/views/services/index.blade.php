@extends('layouts.app')

@section('content')
@php
    $villageName = $profile?->name ?? __('Desa');
    $initialServiceTab = old('submission_channel', session('active_service_tab', 'administrasi'));
    $initialLetterService = old('service_type', session('active_letter_service'));
    $defaultLetterFormTitle = __('Formulir Pengajuan Surat');
    $defaultServicesHeroTitle = __('Layanan Kampung :village', ['village' => $villageName]);
    $defaultServicesHeroSubtitle = __('Nikmati kemudahan layanan digital untuk kebutuhan administrasi, pengaduan, dan informasi publik.');
    $defaultServicesHeroImage = 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?auto=format&fit=crop&w=1920&q=80';
    $configuredServicesHeroTitle = trim((string) data_get($servicesPageSetting ?? [], 'title', ''));
    $configuredServicesHeroSubtitle = trim((string) data_get($servicesPageSetting ?? [], 'subtitle', ''));
    $configuredServicesHeroImage = trim((string) data_get($servicesPageSetting ?? [], 'hero_image_url', ''));
    $servicesHeroTitle = $configuredServicesHeroTitle !== '' ? $configuredServicesHeroTitle : $defaultServicesHeroTitle;
    $servicesHeroSubtitle = $configuredServicesHeroSubtitle !== '' ? $configuredServicesHeroSubtitle : $defaultServicesHeroSubtitle;
    $servicesHeroImage = $configuredServicesHeroImage !== '' ? $configuredServicesHeroImage : $defaultServicesHeroImage;
@endphp

<div class="service-page">
<section
    class="relative h-[300px] overflow-hidden bg-scroll bg-cover bg-center bg-no-repeat text-white sm:h-[400px] sm:bg-fixed"
    style="background-image: url('{{ $servicesHeroImage }}');"
>
    <div class="absolute inset-0 bg-slate-900/45"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/25 via-black/20 to-black/45"></div>
    <div class="service-hero-content relative mx-auto flex h-full max-w-7xl items-center justify-center px-4 pt-20 text-center sm:px-6 lg:px-8">
        <div>
            <h1 class="text-3xl font-black sm:text-5xl">{{ $servicesHeroTitle }}</h1>
            <p class="mx-auto mt-4 max-w-3xl text-sm text-slate-100 sm:text-2xl">
                {{ $servicesHeroSubtitle }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#efeff1] pb-16 pt-8 sm:pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="border-b border-slate-300" data-service-reveal style="--service-reveal-delay: 40ms;">
            <div class="mx-auto flex max-w-3xl flex-wrap justify-center gap-4 pb-1">
                <button
                    type="button"
                    data-service-tab="administrasi"
                    data-service-item
                    data-active="true"
                    class="service-tab inline-flex items-center gap-2 px-3 py-2 text-sm font-bold sm:text-base"
                    style="--service-item-delay: 70ms;"
                >
                    <svg class="h-4 w-4 text-yellow-500 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <path d="M14 2v6h6"></path>
                        <path d="M8 13h8M8 17h5"></path>
                    </svg>
                    {{ __('Administrasi Persuratan') }}
                    <span class="service-tab-line"></span>
                </button>
                <button
                    type="button"
                    data-service-tab="pengaduan"
                    data-service-item
                    data-active="false"
                    class="service-tab inline-flex items-center gap-2 px-3 py-2 text-sm font-bold sm:text-base"
                    style="--service-item-delay: 130ms;"
                >
                    <svg class="h-4 w-4 text-blue-500 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                    {{ __('Pengaduan Masyarakat') }}
                    <span class="service-tab-line"></span>
                </button>
            </div>
        </div>

        @if (session('service_success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" data-service-reveal data-service-card style="--service-reveal-delay: 70ms;">
                {{ session('service_success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700" data-service-reveal data-service-card style="--service-reveal-delay: 95ms;">
                {{ __('Data belum valid. Silakan periksa kolom yang ditandai.') }}
            </div>
        @endif

        <div class="service-content-shell mt-8 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[0_12px_30px_rgba(15,23,42,0.10)]" data-service-reveal style="--service-reveal-delay: 120ms;">
            <div data-service-panel="administrasi" class="service-panel p-6 sm:p-8 lg:p-10">
                <div class="grid gap-8 lg:grid-cols-[1fr_0.9fr]" data-service-reveal style="--service-reveal-delay: 180ms;">
                    <div>
                        <h2 class="text-2xl font-black text-slate-800 sm:text-3xl">{{ __('Administrasi Persuratan Digital') }}</h2>
                        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg sm:leading-8">
                            {{ __('Layanan ini memudahkan masyarakat Kampung :village untuk mengajukan surat secara online.', ['village' => $villageName]) }}
                            {{ __('Pilih jenis surat, isi data dengan benar, dan ajukan permohonan tanpa harus datang ke kantor desa.') }}
                        </p>
                    </div>
                    <div class="grid place-items-center p-2 sm:p-4">
                        <svg class="h-56 w-full max-w-md sm:h-64" viewBox="0 0 360 240" fill="none" aria-hidden="true">
                            <rect x="30" y="28" width="300" height="184" rx="24" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="2"></rect>
                            <rect x="122" y="62" width="116" height="128" rx="14" fill="#EEF2FF" stroke="#C7D2FE" stroke-width="1.5"></rect>
                            <path d="M202 62v24h24" stroke="#6366F1" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M138 100h72M138 116h72M138 132h46" stroke="#818CF8" stroke-width="4" stroke-linecap="round"></path>
                            <path d="M180 180v-34" stroke="#4F46E5" stroke-width="8" stroke-linecap="round"></path>
                            <path d="m166 160 14-14 14 14" stroke="#4F46E5" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"></path>
                            <rect x="108" y="196" width="144" height="10" rx="5" fill="#C7D2FE"></rect>
                        </svg>
                    </div>
                </div>

                <div class="mt-8 grid gap-5 lg:grid-cols-2">
                    <article class="service-letter-panel rounded-2xl border border-blue-100 bg-blue-50/70 p-5" data-service-reveal data-service-card style="--service-reveal-delay: 230ms;">
                        <h3 class="flex items-center gap-2 text-xl font-black text-blue-700 sm:text-2xl">
                            <svg class="h-5 w-5 text-yellow-500 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <path d="M14 2v6h6"></path>
                            </svg>
                            {{ __('Pilih Jenis Surat') }}
                        </h3>
                        <div class="mt-4 space-y-2">
                            @foreach ($letterServices as $service)
                                <button
                                    type="button"
                                    data-letter-item
                                    data-service-item
                                    data-service-card
                                    data-letter-key="{{ $service['key'] }}"
                                    data-letter-title="{{ __($service['form_title']) }}"
                                    class="letter-item w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-left text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:text-blue-700 sm:text-base"
                                    style="--service-item-delay: {{ 160 + ($loop->index * 70) }}ms;"
                                >
                                    {{ __($service['name']) }}
                                </button>
                            @endforeach
                        </div>
                    </article>

                    <article class="service-form-panel rounded-2xl border border-blue-100 bg-slate-50 p-5" data-service-reveal data-service-card style="--service-reveal-delay: 280ms;">
                        <div data-form-empty data-service-item data-service-card style="--service-item-delay: 110ms;" class="grid min-h-[320px] place-items-center rounded-xl border border-dashed border-slate-300 bg-white/70 p-6 text-center">
                            <div>
                                <svg class="mx-auto h-10 w-10 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <path d="M14 2v6h6"></path>
                                    <path d="M8 13h8M8 17h5"></path>
                                </svg>
                                <p class="mt-4 text-lg font-semibold text-slate-600">{{ __('Pilih jenis surat untuk memulai pengajuan.') }}</p>
                            </div>
                        </div>

                        <div data-form-container class="letter-form-container hidden">
                            <h3 data-form-title class="text-xl font-black text-blue-700 sm:text-2xl">
                                {{ $defaultLetterFormTitle }}
                            </h3>
                            <form class="mt-5 space-y-3" action="{{ route('services.letter-submissions.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="submission_channel" value="administrasi">
                                <input type="hidden" name="service_type" data-form-service value="{{ old('service_type') }}">

                                <div class="form-anim-item" style="--stagger: 40ms">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Nama Lengkap') }}</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        value="{{ old('full_name') }}"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('Masukkan nama lengkap') }}"
                                    >
                                    @error('full_name')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-anim-item" style="--stagger: 90ms">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">NIK</label>
                                    <input
                                        type="text"
                                        name="nik"
                                        value="{{ old('nik') }}"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('Masukkan NIK') }}"
                                    >
                                    @error('nik')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid gap-3 sm:grid-cols-2 form-anim-item" style="--stagger: 140ms">
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Nomor WhatsApp') }}</label>
                                        <input
                                            type="text"
                                            name="whatsapp"
                                            value="{{ old('whatsapp') }}"
                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                            placeholder="08xxxxxxxxxx"
                                        >
                                        @error('whatsapp')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ old('email') }}"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('contoh@email.com') }}"
                                    >
                                        @error('email')
                                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-anim-item" style="--stagger: 190ms">
                                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Keperluan') }}</label>
                                    <textarea
                                        rows="4"
                                        name="purpose"
                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('Tuliskan keperluan pengajuan surat...') }}"
                                    >{{ old('purpose') }}</textarea>
                                    @error('purpose')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                    @error('service_type')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="form-anim-item inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(37,99,235,0.34)] transition hover:bg-blue-700" style="--stagger: 240ms">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 2 11 13"></path>
                                        <path d="m22 2-7 20-4-9-9-4 20-7Z"></path>
                                    </svg>
                                    {{ __('Ajukan Permohonan') }}
                                </button>
                            </form>
                        </div>
                    </article>
                </div>
            </div>

            <div data-service-panel="pengaduan" class="service-panel hidden p-6 sm:p-8 lg:p-10">
                <div>
                    <div class="grid gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-center" data-service-reveal style="--service-reveal-delay: 180ms;">
                        <div>
                            <h2 class="text-3xl font-black text-slate-800 sm:text-4xl">{{ __('Pengaduan Masyarakat') }}</h2>
                            <p class="mt-3 max-w-2xl text-base leading-8 text-slate-600">
                                {{ __('Laporkan keluhan, aspirasi, atau permasalahan yang terjadi di Kampung :village secara online.', ['village' => $villageName]) }}
                                {{ __('Setiap pengaduan akan ditindaklanjuti secara transparan.') }}
                            </p>
                        </div>

                        <div class="grid place-items-center lg:justify-items-end">
                            <img
                                src="{{ asset('images/illustrations/on_the_office_fbfs.svg') }}"
                                alt="{{ __('Ilustrasi petugas layanan pengaduan masyarakat') }}"
                                class="h-52 w-full max-w-md object-contain sm:h-56"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>

                    <div class="mt-8 pt-2" data-service-reveal style="--service-reveal-delay: 240ms;">
                        <h3 class="flex items-center gap-2 text-xl font-black text-blue-700 sm:text-2xl">
                            <svg class="h-5 w-5 text-yellow-500 sm:h-6 sm:w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            {{ __('Formulir Pengaduan') }}
                        </h3>

                        <form class="mt-5 space-y-4" action="{{ route('services.complaint-submissions.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="submission_channel" value="pengaduan">
                            <div class="grid gap-4 sm:grid-cols-2" data-service-item style="--service-item-delay: 120ms;">
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Nama Lengkap') }}</label>
                                    <input
                                        type="text"
                                        name="full_name"
                                        value="{{ old('full_name') }}"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('Masukkan nama lengkap') }}"
                                    >
                                    @error('full_name')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        placeholder="{{ __('contoh@email.com') }}"
                                    >
                                    @error('email')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div data-service-item style="--service-item-delay: 190ms;">
                                <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Nomor WhatsApp') }}</label>
                                <input
                                    type="text"
                                    name="whatsapp"
                                    value="{{ old('whatsapp') }}"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="08xxxxxxxxxx"
                                >
                                @error('whatsapp')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div data-service-item style="--service-item-delay: 260ms;">
                                <label class="mb-1 block text-sm font-semibold text-slate-700">{{ __('Isi Pengaduan') }}</label>
                                <textarea
                                    rows="5"
                                    name="complaint"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                    placeholder="{{ __('Tuliskan pengaduan Anda...') }}"
                                >{{ old('complaint') }}</textarea>
                                @error('complaint')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button type="submit" data-service-item style="--service-item-delay: 330ms;" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-[0_8px_18px_rgba(37,99,235,0.34)] transition hover:bg-blue-700">
                                <svg class="h-4 w-4 text-yellow-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 2 11 13"></path>
                                    <path d="m22 2-7 20-4-9-9-4 20-7Z"></path>
                                </svg>
                                {{ __('Kirim Pengaduan') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<style>
    .service-page [data-service-reveal] {
        opacity: 0;
        transform: translate3d(0, 16px, 0);
        will-change: opacity, transform;
        transition:
            opacity .78s cubic-bezier(.22, 1, .36, 1),
            transform .78s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--service-reveal-delay, 0ms);
    }

    .service-page [data-service-reveal].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .service-page [data-service-item] {
        opacity: 0;
        transform: translate3d(0, 12px, 0);
        will-change: opacity, transform;
        transition:
            opacity .82s cubic-bezier(.22, 1, .36, 1),
            transform .82s cubic-bezier(.22, 1, .36, 1);
        transition-delay: var(--service-item-delay, 0ms);
    }

    .service-page [data-service-item].is-visible {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }

    .service-hero-content > * {
        opacity: 0;
        transform: translate3d(0, 26px, 0);
        animation: serviceHeroRise .86s cubic-bezier(.22, 1, .36, 1) forwards;
    }

    .service-hero-content > h1 {
        animation-delay: 120ms;
    }

    .service-hero-content > p {
        animation-delay: 240ms;
    }

    .service-tab {
        position: relative;
        color: #64748b;
    }

    .service-tab[data-active="true"] {
        color: #2563eb;
    }

    .service-tab-line {
        position: absolute;
        left: 0;
        right: 0;
        bottom: -2px;
        height: 3px;
        border-radius: 9999px;
        background: #facc15;
        opacity: 0;
        transform: scaleX(0.5);
        transition: transform .25s ease, opacity .25s ease;
    }

    .service-tab[data-active="true"] .service-tab-line {
        opacity: 1;
        transform: scaleX(1);
    }

    .service-panel {
        animation: panelFade .46s cubic-bezier(.22, 1, .36, 1);
    }

    .letter-item.is-active {
        color: #ffffff;
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
    }

    :root[data-theme='dark'] .service-content-shell {
        border-color: rgba(125, 211, 252, 0.28);
        background:
            radial-gradient(140% 90% at 8% -18%, rgba(56, 189, 248, 0.14), transparent 58%),
            linear-gradient(156deg, #0a162a 0%, #112946 54%, #173d63 100%);
        box-shadow: 0 20px 46px rgba(2, 6, 23, 0.54), 0 8px 22px rgba(14, 116, 144, 0.28);
    }

    :root[data-theme='dark'] .service-content-shell h2 {
        color: #f0f8ff !important;
    }

    :root[data-theme='dark'] .service-content-shell p {
        color: #cdddf4;
    }

    :root[data-theme='dark'] .service-tab {
        color: #a9bfdd;
    }

    :root[data-theme='dark'] .service-tab[data-active="true"] {
        color: #7dd3fc;
    }

    :root[data-theme='dark'] .service-tab-line {
        background: #7dd3fc;
        box-shadow: 0 0 10px rgba(56, 189, 248, 0.6);
    }

    :root[data-theme='dark'] .service-letter-panel {
        border-color: rgba(125, 211, 252, 0.3);
        background: linear-gradient(162deg, rgba(18, 42, 74, 0.94), rgba(13, 33, 58, 0.97)) !important;
    }

    :root[data-theme='dark'] .service-form-panel {
        border-color: rgba(125, 211, 252, 0.3);
        background: linear-gradient(160deg, rgba(10, 28, 50, 0.95), rgba(8, 24, 45, 0.98)) !important;
    }

    :root[data-theme='dark'] .service-letter-panel h3,
    :root[data-theme='dark'] .service-form-panel h3 {
        color: #8fd3ff !important;
    }

    :root[data-theme='dark'] .letter-item {
        border-color: rgba(125, 211, 252, 0.2);
        background: linear-gradient(180deg, rgba(18, 44, 78, 0.92), rgba(16, 38, 68, 0.94)) !important;
        color: #e6f1ff;
    }

    :root[data-theme='dark'] .letter-item:hover {
        border-color: rgba(125, 211, 252, 0.58);
        background: linear-gradient(180deg, rgba(25, 58, 98, 0.95), rgba(20, 48, 83, 0.96)) !important;
        color: #f0f9ff;
    }

    :root[data-theme='dark'] .letter-item.is-active {
        color: #f8fbff;
        background: linear-gradient(150deg, #1d4e96, #2563b8);
        border-color: rgba(147, 197, 253, 0.82);
        box-shadow: 0 10px 22px rgba(2, 6, 23, 0.42), 0 4px 16px rgba(37, 99, 235, 0.36);
    }

    :root[data-theme='dark'] [data-form-empty] {
        border-color: rgba(125, 211, 252, 0.26);
        background: linear-gradient(168deg, rgba(10, 28, 50, 0.88), rgba(8, 23, 43, 0.92)) !important;
    }

    :root[data-theme='dark'] [data-form-empty] svg {
        color: #8fd3ff;
    }

    :root[data-theme='dark'] .service-form-panel label {
        color: #acc6e7 !important;
    }

    :root[data-theme='dark'] .service-form-panel input,
    :root[data-theme='dark'] .service-form-panel textarea {
        border-color: rgba(125, 211, 252, 0.24) !important;
        background: rgba(12, 31, 56, 0.88) !important;
        color: #eff6ff !important;
    }

    :root[data-theme='dark'] .service-form-panel input:focus,
    :root[data-theme='dark'] .service-form-panel textarea:focus {
        border-color: rgba(125, 211, 252, 0.68) !important;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.18);
    }

    :root[data-theme='dark'] .service-form-panel input::placeholder,
    :root[data-theme='dark'] .service-form-panel textarea::placeholder {
        color: #89a5cb !important;
    }

    .letter-form-container.is-entering {
        animation: formArrive .62s cubic-bezier(.22, 1, .36, 1);
    }

    .letter-form-container.is-entering .form-anim-item {
        opacity: 0;
        transform: translateY(12px);
        animation: fieldArrive .48s cubic-bezier(.22, 1, .36, 1) forwards;
        animation-delay: var(--stagger, 0ms);
    }

    @keyframes panelFade {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes formArrive {
        from {
            opacity: 0;
            transform: translateY(20px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    @keyframes fieldArrive {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes serviceHeroRise {
        from {
            opacity: 0;
            transform: translate3d(0, 28px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .service-page [data-service-reveal] {
            opacity: 1 !important;
            transform: none !important;
            filter: none !important;
            transition: none !important;
        }

        .service-page [data-service-item] {
            opacity: 1 !important;
            transform: none !important;
            transition: none !important;
        }

        .service-hero-content > * {
            opacity: 1 !important;
            transform: none !important;
            animation: none !important;
        }
    }
</style>

<script>
    (() => {
        const initialServiceTab = @json($initialServiceTab);
        const initialLetterService = @json($initialLetterService);
        const tabs = Array.from(document.querySelectorAll('[data-service-tab]'));
        const panels = Array.from(document.querySelectorAll('[data-service-panel]'));
        const letterItems = Array.from(document.querySelectorAll('[data-letter-item]'));
        const formTitle = document.querySelector('[data-form-title]');
        const formServiceInput = document.querySelector('[data-form-service]');
        const formEmpty = document.querySelector('[data-form-empty]');
        const formContainer = document.querySelector('[data-form-container]');
        const revealTargets = Array.from(document.querySelectorAll('[data-service-reveal], [data-service-item]'));
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const supportsObserver = 'IntersectionObserver' in window;
        let revealObserver = null;
        let revealFallbackHandler = null;

        const revealElement = (element) => {
            if (element.classList.contains('is-visible')) {
                return;
            }
            element.classList.add('is-visible');
            if (revealObserver) {
                revealObserver.unobserve(element);
            }
        };

        const revealVisibleElements = (scope = document) => {
            const scopedTargets = Array.from(scope.querySelectorAll('[data-service-reveal], [data-service-item]'));
            scopedTargets.forEach((element) => {
                const rect = element.getBoundingClientRect();
                if (rect.top <= window.innerHeight * 0.95) {
                    revealElement(element);
                }
            });
        };

        if (prefersReducedMotion) {
            revealTargets.forEach(revealElement);
        } else {
            if (supportsObserver) {
                revealObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) {
                            return;
                        }

                        revealElement(entry.target);
                    });
                }, {
                    threshold: 0.08,
                    rootMargin: '0px 0px -6% 0px',
                });

                revealTargets.forEach((element) => {
                    revealObserver.observe(element);
                });
            }

            revealFallbackHandler = () => revealVisibleElements(document);
            revealVisibleElements(document);
            window.addEventListener('scroll', revealFallbackHandler, { passive: true });
            window.addEventListener('resize', revealFallbackHandler, { passive: true });
        }

        const setActiveTab = (targetId) => {
            tabs.forEach((tab) => {
                const active = tab.dataset.serviceTab === targetId;
                tab.dataset.active = active ? 'true' : 'false';
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.dataset.servicePanel !== targetId);
            });

            window.requestAnimationFrame(() => {
                const activePanel = panels.find((panel) => panel.dataset.servicePanel === targetId);
                if (activePanel) {
                    revealVisibleElements(activePanel);
                }
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => setActiveTab(tab.dataset.serviceTab));
        });

        const setActiveLetter = (targetButton) => {
            letterItems.forEach((item) => item.classList.remove('is-active'));
            targetButton.classList.add('is-active');

            if (formTitle) {
                formTitle.textContent = targetButton.dataset.letterTitle || @json($defaultLetterFormTitle);
            }

            if (formServiceInput) {
                formServiceInput.value = targetButton.dataset.letterKey || '';
            }

            if (formEmpty) {
                formEmpty.classList.add('hidden');
            }

            if (formContainer) {
                formContainer.classList.remove('hidden');
                formContainer.classList.remove('is-entering');
                void formContainer.offsetWidth;
                formContainer.classList.add('is-entering');
            }
        };

        letterItems.forEach((button) => {
            button.addEventListener('click', () => setActiveLetter(button));
        });

        const hasInitialTab = tabs.some((tab) => tab.dataset.serviceTab === initialServiceTab);
        setActiveTab(hasInitialTab ? initialServiceTab : 'administrasi');

        if (initialLetterService) {
            const selectedLetterButton = letterItems.find((button) => button.dataset.letterKey === initialLetterService);

            if (selectedLetterButton) {
                setActiveLetter(selectedLetterButton);
            }
        }

        window.addEventListener('beforeunload', () => {
            if (revealFallbackHandler) {
                window.removeEventListener('scroll', revealFallbackHandler);
                window.removeEventListener('resize', revealFallbackHandler);
            }
        });

        const forms = Array.from(document.querySelectorAll('form[action]'));
        const serviceForms = forms.filter((form) => {
            const action = form.getAttribute('action') || '';
            return action.includes('/layanan/administrasi-persuratan') || action.includes('/layanan/pengaduan');
        });

        const focusableSelector = 'input, textarea, select, button, [tabindex]:not([tabindex="-1"])';

        serviceForms.forEach((form) => {
            form.addEventListener('keydown', (event) => {
                const target = event.target;
                if (!(target instanceof HTMLElement)) {
                    return;
                }

                // Hindari submit tidak sengaja saat tekan Enter di field input.
                if (event.key === 'Enter' && target.tagName !== 'TEXTAREA') {
                    event.preventDefault();

                    const focusables = Array.from(form.querySelectorAll(focusableSelector))
                        .filter((el) => el instanceof HTMLElement)
                        .filter((el) => !el.hasAttribute('disabled'))
                        .filter((el) => el.getAttribute('type') !== 'hidden');

                    const currentIndex = focusables.indexOf(target);
                    const nextElement = focusables[currentIndex + 1];
                    if (nextElement instanceof HTMLElement) {
                        nextElement.focus();
                    }
                }
            });

            form.addEventListener('submit', (event) => {
                if (form.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                const action = form.getAttribute('action') || '';
                if (action.includes('/layanan/administrasi-persuratan')) {
                    const selectedService = form.querySelector('[data-form-service]');
                    if (selectedService instanceof HTMLInputElement && !selectedService.value) {
                        event.preventDefault();
                        if (formEmpty) {
                            formEmpty.classList.remove('hidden');
                        }
                        if (formContainer) {
                            formContainer.classList.add('hidden');
                        }
                        return;
                    }
                }

                form.dataset.submitting = '1';
                const submitButton = form.querySelector('button[type="submit"]');
                if (submitButton instanceof HTMLButtonElement) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-70', 'cursor-not-allowed');
                }
            });
        });
    })();
</script>
@endsection
