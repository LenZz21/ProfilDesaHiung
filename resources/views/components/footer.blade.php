@php
    $profileRecord = $profile ?? \App\Models\VillageProfile::query()->first();
    $profileName = trim((string) data_get($profileRecord, 'name', __('Kampung Hiung')));
    $villageName = preg_replace('/^(desa|kampung)\s+/i', '', $profileName) ?: $profileName;
    $address = trim((string) data_get($profileRecord, 'address', ''));
    $email = trim((string) data_get($profileRecord, 'email', ''));
    $whatsapp = trim((string) data_get($profileRecord, 'whatsapp', ''));
    $facebookUrl = trim((string) data_get($profileRecord, 'facebook_url', ''));
    $instagramUrl = trim((string) data_get($profileRecord, 'instagram_url', ''));
    $xUrl = trim((string) data_get($profileRecord, 'x_url', ''));

    $normalizePhone = static function (string $rawPhone): string {
        $digits = preg_replace('/\D+/', '', $rawPhone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }

        if (str_starts_with($digits, '62')) {
            return $digits;
        }

        if (str_starts_with($digits, '8')) {
            return '62' . $digits;
        }

        return $digits;
    };

    $waNumber = $normalizePhone($whatsapp);
    $waLink = $waNumber !== '' ? 'https://wa.me/' . $waNumber : null;

    $quickLinks = [
        ['label' => __('ui.home'), 'url' => route('home')],
        ['label' => __('ui.profile_short'), 'url' => route('profile.index')],
        ['label' => __('ui.news'), 'url' => route('posts.index')],
        ['label' => __('ui.services'), 'url' => route('services.index')],
    ];

    $officials = \App\Models\Official::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get(['id', 'position', 'phone', 'structure_group']);
    $resolveStructureGroup = static fn (\App\Models\Official $official): string => $official->structure_group
        ?: \App\Models\Official::detectStructureGroupFromPosition($official->position);
    $leaderOfficial = $officials->first(
        fn (\App\Models\Official $official) => $resolveStructureGroup($official) === \App\Models\Official::GROUP_LEADER
    );
    $secretaryOfficial = $officials->first(
        fn (\App\Models\Official $official) => $official->id !== $leaderOfficial?->id
            && $resolveStructureGroup($official) === \App\Models\Official::GROUP_SECRETARY
    );

    $importantNumbers = [
        [
            'label' => $leaderOfficial?->position ?: 'Kapitalaung',
            'value' => trim((string) ($leaderOfficial?->phone ?? '')) ?: '-',
        ],
        [
            'label' => $secretaryOfficial?->position ?: __('Sekretaris Kampung'),
            'value' => trim((string) ($secretaryOfficial?->phone ?? '')) ?: '-',
        ],
    ];
@endphp

<footer class="site-footer relative overflow-hidden bg-gradient-to-r from-slate-800 via-slate-700 to-slate-600 text-white">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-white/10 to-transparent"></div>
    <div class="pointer-events-none absolute -right-16 top-6 h-40 w-40 rounded-full bg-slate-200/10 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 pb-10 pt-12 sm:gap-10 sm:pb-12 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <div class="flex items-center gap-3">
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-slate-300/20 text-sm font-black shadow-lg ring-1 ring-slate-200/40">DS</span>
                    <p class="text-base font-extrabold leading-tight">{{ __('ui.government_of_village') }} {{ $villageName }}</p>
                </div>
                <p class="mt-4 max-w-sm text-sm leading-6 text-slate-200">
                    {{ $address !== '' ? $address : __('ui.address_not_available') }}
                </p>
                <a
                    href="{{ route('profile.index') }}"
                    class="mt-5 inline-flex items-center rounded-full border border-slate-300/60 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-slate-200 transition hover:border-white hover:text-white"
                >
                    {{ __('ui.profile_link') }}
                </a>
            </div>

            <div class="lg:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-200">{{ __('ui.quick_links') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                    @foreach ($quickLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}" class="inline-flex items-center transition hover:translate-x-0.5 hover:text-white">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-200">{{ __('ui.important_numbers') }}</h3>
                <p class="mt-1 text-xs text-slate-300/90">{{ __('ui.click_number_whatsapp') }}</p>
                <ul class="mt-4 space-y-3 text-sm text-slate-200">
                    @foreach ($importantNumbers as $contact)
                        @php
                            $phoneNumber = $normalizePhone($contact['value']);
                            $phoneLink = $phoneNumber !== '' ? 'https://wa.me/' . $phoneNumber : null;
                        @endphp
                        <li class="group/phone">
                            <p class="font-semibold text-white/95">{{ $contact['label'] }}</p>
                            @if ($phoneLink)
                                <a
                                    href="{{ $phoneLink }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-1 inline-flex items-center gap-2 rounded-lg border border-transparent px-2 py-1 text-slate-200 transition-all duration-300 hover:-translate-y-0.5 hover:border-slate-300/60 hover:bg-white/10 hover:text-white"
                                >
                                    <svg class="h-3.5 w-3.5 transition-transform duration-300 group-hover/phone:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 19l-4 1 1-4a8 8 0 1 1 3 3z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 10.5c0 2 2 4 4 4"/>
                                    </svg>
                                    <span class="break-all tracking-normal transition-all duration-300 group-hover/phone:tracking-wide">{{ $contact['value'] }}</span>
                                </a>
                            @else
                                <span class="break-all">{{ $contact['value'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-slate-200">{{ __('ui.contact_menu') }}</h3>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-200">
                    <li>
                        <span class="font-semibold text-white/95">{{ __('ui.email') }}:</span>
                        @if ($email !== '')
                            <a href="mailto:{{ $email }}" class="break-all transition hover:text-white">{{ $email }}</a>
                        @else
                            <span>-</span>
                        @endif
                    </li>
                    <li>
                        <span class="font-semibold text-white/95">{{ __('ui.wa') }}:</span>
                        @if ($whatsapp !== '' && $waLink)
                            <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="break-all transition hover:text-white">{{ $whatsapp }}</a>
                        @else
                            <span>-</span>
                        @endif
                    </li>
                </ul>

                <div class="mt-4 flex items-center gap-2.5">
                    @if ($facebookUrl !== '')
                        <a
                            href="{{ $facebookUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ __('Facebook kampung') }}"
                            class="grid h-9 w-9 place-items-center rounded-full border border-slate-200/80 text-slate-200 transition hover:-translate-y-0.5 hover:border-white hover:text-white"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M22 12.07C22 6.5 17.52 2 12 2S2 6.5 2 12.07c0 5.03 3.66 9.2 8.44 9.93v-7.03H7.9v-2.9h2.54V9.84c0-2.52 1.49-3.92 3.78-3.92 1.1 0 2.25.2 2.25.2v2.47h-1.27c-1.25 0-1.64.78-1.64 1.58v1.9h2.8l-.45 2.9h-2.35V22c4.78-.73 8.44-4.9 8.44-9.93z"/>
                            </svg>
                        </a>
                    @endif

                    @if ($instagramUrl !== '')
                        <a
                            href="{{ $instagramUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ __('Instagram kampung') }}"
                            class="grid h-9 w-9 place-items-center rounded-full border border-slate-200/80 text-slate-200 transition hover:-translate-y-0.5 hover:border-white hover:text-white"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm8.5 1.8h-8.5A3.95 3.95 0 0 0 3.8 7.75v8.5a3.95 3.95 0 0 0 3.95 3.95h8.5a3.95 3.95 0 0 0 3.95-3.95v-8.5a3.95 3.95 0 0 0-3.95-3.95z"/>
                                <path d="M12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.8a3.2 3.2 0 1 0 0 6.4 3.2 3.2 0 0 0 0-6.4zM17.45 6.1a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4z"/>
                            </svg>
                        </a>
                    @endif

                    @if ($xUrl !== '')
                        <a
                            href="{{ $xUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            aria-label="{{ __('X kampung') }}"
                            class="grid h-9 w-9 place-items-center rounded-full border border-slate-200/80 text-slate-200 transition hover:-translate-y-0.5 hover:border-white hover:text-white"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M18.9 2H22l-6.8 7.8L23 22h-6.1l-4.8-6.4L6.5 22H3.4l7.2-8.2L2 2h6.2l4.3 5.8L18.9 2zm-1.1 18h1.7L7.3 3.9H5.5L17.8 20z"/>
                            </svg>
                        </a>
                    @endif

                    @if ($facebookUrl === '' && $instagramUrl === '' && $xUrl === '')
                        <span class="text-xs text-slate-300/90">{{ __('ui.social_links_empty') }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-2 border-t border-slate-400/50 py-4 text-center text-xs text-slate-200 sm:flex-row sm:items-center sm:justify-between sm:text-left">
            <p class="break-words">&copy; {{ now()->year }} {{ __('ui.government_of_village') }} {{ $villageName }}. {{ __('ui.rights_reserved') }}</p>
            <a href="#" class="inline-flex items-center justify-center font-semibold transition hover:text-white sm:justify-start">{{ __('ui.back_to_top') }}</a>
        </div>
    </div>
</footer>
