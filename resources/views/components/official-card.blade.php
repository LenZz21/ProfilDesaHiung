@props([
    'official',
    'extraClass' => '',
])

@php
    $rawPhone = trim((string) ($official->phone ?? ''));
    $phoneDigits = preg_replace('/\D+/', '', $rawPhone);
    $waNumber = '';

    if ($phoneDigits !== '') {
        if (str_starts_with($phoneDigits, '0')) {
            $waNumber = '62' . substr($phoneDigits, 1);
        } elseif (str_starts_with($phoneDigits, '62')) {
            $waNumber = $phoneDigits;
        } else {
            $waNumber = $phoneDigits;
        }
    }

    $waUrl = $waNumber !== '' ? 'https://wa.me/' . $waNumber : null;
    $photoUrl = $official->photo ? Storage::url($official->photo) : null;
    $initials = collect(preg_split('/\s+/', trim((string) ($official->name ?? ''))))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');
@endphp

<article
    class="official-card group flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-3 shadow-[0_12px_34px_rgba(15,23,42,0.14)] transition-all duration-300 hover:-translate-y-1 hover:border-blue-300/70 hover:shadow-[0_20px_46px_rgba(15,23,42,0.22)] {{ $extraClass }}"
    data-official-card
    data-mobile-contact="closed"
>
    <div class="official-card-media relative aspect-[4/5] overflow-hidden rounded-xl bg-slate-100" data-official-card-media>
        @if ($photoUrl)
            <img
                src="{{ $photoUrl }}"
                alt="{{ $official->name }}"
                class="official-card-visual official-card-photo h-full w-full object-cover object-center transition-transform duration-700 group-hover:scale-[1.05]"
            >
        @else
            <div class="official-card-visual official-card-placeholder absolute inset-0 bg-gradient-to-br from-cyan-600 via-teal-600 to-blue-700 transition-transform duration-700 group-hover:scale-[1.05]">
                <div class="official-card-placeholder-glow absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.24),transparent_52%)]"></div>
                <div class="absolute inset-0 grid place-items-center p-4 text-center text-white">
                    <div class="space-y-2">
                        <span class="official-card-placeholder-initial mx-auto grid h-16 w-16 place-items-center rounded-full border border-white/55 bg-white/15 text-2xl font-black shadow-[0_10px_28px_rgba(2,6,23,0.34)]">
                            {{ $initials !== '' ? $initials : 'PD' }}
                        </span>
                        <p class="official-card-placeholder-text text-xs font-semibold uppercase tracking-[0.14em] text-white/90">{{ __('ui.photo_not_available') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="official-card-overlay pointer-events-none absolute inset-0 bg-gradient-to-b from-blue-900/20 via-blue-900/56 to-slate-950/86 opacity-70 transition-opacity duration-500 sm:opacity-0 sm:group-hover:opacity-100"></div>

        <div class="official-card-actions absolute inset-x-3 bottom-3 translate-y-0 opacity-100 transition-all duration-500 sm:inset-x-4 sm:bottom-4 sm:translate-y-3 sm:opacity-0 sm:group-hover:translate-y-0 sm:group-hover:opacity-100">
            @if ($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="official-card-phone group/wa flex items-center justify-center gap-2 text-white/95" aria-label="{{ __('ui.whatsapp_contact_aria', ['name' => $official->name]) }}">
                    <span class="official-card-phone-icon text-white/90 transition-all duration-300 group-hover/wa:scale-110 group-hover/wa:text-[#25D366]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M20.52 3.48A11.88 11.88 0 0 0 12.06 0C5.52 0 .2 5.32 .2 11.86c0 2.09.55 4.14 1.59 5.94L0 24l6.37-1.67a11.79 11.79 0 0 0 5.69 1.45h.01c6.54 0 11.86-5.32 11.86-11.86a11.8 11.8 0 0 0-3.41-8.44Zm-8.46 18.3h-.01a9.88 9.88 0 0 1-5.04-1.38l-.36-.21-3.78.99 1.01-3.68-.23-.38a9.87 9.87 0 0 1-1.52-5.26c0-5.45 4.43-9.88 9.88-9.88 2.64 0 5.12 1.03 6.98 2.89a9.8 9.8 0 0 1 2.89 6.99c0 5.45-4.43 9.88-9.88 9.88Zm5.41-7.42c-.3-.15-1.77-.87-2.04-.96-.27-.1-.47-.15-.66.15-.2.3-.77.96-.95 1.16-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.41-1.49a8.98 8.98 0 0 1-1.68-2.08c-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.66-1.6-.9-2.2-.24-.58-.48-.5-.66-.51-.17-.01-.37-.01-.57-.01s-.52.08-.8.37c-.27.3-1.04 1.02-1.04 2.49s1.07 2.89 1.22 3.09c.15.2 2.1 3.21 5.09 4.5.71.31 1.27.49 1.7.63.71.23 1.35.2 1.86.12.57-.08 1.77-.72 2.02-1.41.25-.69.25-1.28.17-1.41-.07-.13-.27-.2-.57-.35Z"/>
                        </svg>
                    </span>
                    <span class="official-card-phone-text break-all text-xs font-semibold text-white/95 transition-colors duration-300 group-hover/wa:text-[#25D366] sm:text-sm">{{ $official->phone }}</span>
                </a>
            @else
                <div class="official-card-phone group/wa flex items-center justify-center gap-2 text-white/95">
                    <span class="official-card-phone-text text-xs font-semibold text-white/95 transition-colors duration-300 group-hover/wa:text-[#25D366] sm:text-sm">{{ __('ui.contact_not_available') }}</span>
                </div>
            @endif

            <div class="mx-auto mt-3 h-0.5 w-16 rounded bg-yellow-400"></div>
            <div class="official-card-social mt-3 flex items-center justify-center gap-3 text-white sm:gap-4">
                @if ($official->instagram_url)
                    <a href="{{ $official->instagram_url }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="official-card-social-link transition-all duration-300 text-white/90 hover:scale-110 hover:text-[#E1306C]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
                            <rect x="3.5" y="3.5" width="17" height="17" rx="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <circle cx="17.2" cy="6.8" r="1"></circle>
                        </svg>
                    </a>
                @else
                    <span aria-label="Instagram" class="official-card-social-link text-white/45">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" aria-hidden="true">
                            <rect x="3.5" y="3.5" width="17" height="17" rx="5"></rect>
                            <circle cx="12" cy="12" r="4"></circle>
                            <circle cx="17.2" cy="6.8" r="1"></circle>
                        </svg>
                    </span>
                @endif

                @if ($official->facebook_url)
                    <a href="{{ $official->facebook_url }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="official-card-social-link transition-all duration-300 text-white/90 hover:scale-110 hover:text-[#1877F2]">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.5 21v-7h2.3l.4-2.8h-2.7V9.4c0-.8.2-1.4 1.4-1.4H16V5.5c-.2 0-1-.1-2-.1-2 0-3.4 1.2-3.4 3.5v2.3H8.4V14h2.2v7h2.9Z"/>
                        </svg>
                    </a>
                @else
                    <span aria-label="Facebook" class="official-card-social-link text-white/45">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.5 21v-7h2.3l.4-2.8h-2.7V9.4c0-.8.2-1.4 1.4-1.4H16V5.5c-.2 0-1-.1-2-.1-2 0-3.4 1.2-3.4 3.5v2.3H8.4V14h2.2v7h2.9Z"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="official-card-meta flex flex-1 flex-col pt-3 text-center">
        <p class="official-card-name line-clamp-2 text-lg font-black leading-tight tracking-tight text-slate-900 sm:text-xl">{{ $official->name }}</p>
        <p class="official-card-position mt-1 line-clamp-2 text-sm font-semibold text-slate-600">{{ $official->position }}</p>
    </div>
</article>

@once
    <style>
        @media (max-width: 639.98px) {
            .official-card[data-mobile-contact='closed'] .official-card-overlay {
                opacity: 0;
            }

            .official-card[data-mobile-contact='closed'] .official-card-actions {
                opacity: 0;
                transform: translate3d(0, 0.85rem, 0);
                pointer-events: none;
            }

            .official-card[data-mobile-contact='open'] .official-card-overlay {
                opacity: 0.7;
            }

            .official-card[data-mobile-contact='open'] .official-card-actions {
                opacity: 1;
                transform: translate3d(0, 0, 0);
                pointer-events: auto;
            }

            .official-card .official-card-phone {
                width: 100%;
                justify-content: center;
                text-align: center;
                gap: 0.55rem;
            }

            .official-card .official-card-phone-icon svg {
                width: 1.35rem;
                height: 1.35rem;
            }

            .official-card .official-card-phone-text {
                font-size: 0.9rem;
                line-height: 1.25;
            }

            .official-card .official-card-social {
                justify-content: center;
                gap: 0.9rem;
            }

            .official-card .official-card-social-link svg {
                width: 1.55rem;
                height: 1.55rem;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileQuery = window.matchMedia('(max-width: 639.98px)');
            const cards = document.querySelectorAll('[data-official-card]');

            const syncCardState = () => {
                cards.forEach((card) => {
                    card.dataset.mobileContact = mobileQuery.matches ? (card.dataset.mobileContact || 'closed') : 'closed';
                });
            };

            syncCardState();
            mobileQuery.addEventListener?.('change', syncCardState);

            cards.forEach((card) => {
                const media = card.querySelector('[data-official-card-media]');
                if (!media) {
                    return;
                }

                media.addEventListener('click', (event) => {
                    if (!mobileQuery.matches) {
                        return;
                    }

                    if (event.target instanceof Element && event.target.closest('a')) {
                        return;
                    }

                    card.dataset.mobileContact = card.dataset.mobileContact === 'open' ? 'closed' : 'open';
                });
            });
        });
    </script>
@endonce
