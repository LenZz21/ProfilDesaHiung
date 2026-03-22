@extends('layouts.app')

@section('content')
@php
    $galleryItems = collect($gallery->items ?? [])
        ->filter(fn ($item) => is_array($item) && filled(data_get($item, 'image')))
        ->sortBy(fn (array $item) => (int) data_get($item, 'sort_order', 0))
        ->values();
@endphp

<style>
    .gallery-item-card {
        animation: gallery-fade-in .6s ease both;
    }

    .gallery-lightbox {
        position: fixed;
        inset: 0;
        z-index: 80;
        display: grid;
        place-items: center;
        padding: 1.25rem;
        background: rgba(2, 6, 23, 0.86);
        backdrop-filter: blur(3px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.24s ease;
    }

    .gallery-lightbox.is-open {
        opacity: 1;
        pointer-events: auto;
    }

    .gallery-lightbox-stage {
        position: relative;
        display: grid;
        place-items: center;
        width: min(92vw, 1220px);
        height: min(84vh, 760px);
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.34);
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.78), rgba(15, 23, 42, 0.7));
        overflow: hidden;
        touch-action: none;
    }

    .gallery-lightbox-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transform-origin: center center;
        transition: transform 0.18s cubic-bezier(0.22, 1, 0.36, 1);
        user-select: none;
        -webkit-user-drag: none;
        cursor: zoom-in;
        will-change: transform;
    }

    .gallery-lightbox-image.is-dragging {
        transition: none;
    }

    .gallery-lightbox-toolbar {
        position: absolute;
        right: 0.75rem;
        top: 0.75rem;
        z-index: 5;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .gallery-lightbox-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 9999px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        background: rgba(15, 23, 42, 0.68);
        color: #f8fafc;
        font-size: 1.05rem;
        font-weight: 700;
        line-height: 1;
        transition: background-color 0.2s ease, transform 0.2s ease;
    }

    .gallery-lightbox-btn:hover {
        background: rgba(30, 41, 59, 0.9);
        transform: translateY(-1px);
    }

    .gallery-lightbox-caption {
        position: absolute;
        left: 0.9rem;
        right: 0.9rem;
        bottom: 0.85rem;
        z-index: 5;
        border-radius: 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.2);
        background: rgba(15, 23, 42, 0.68);
        padding: 0.5rem 0.75rem;
        color: #e2e8f0;
        font-size: 0.82rem;
        font-weight: 500;
        line-height: 1.3rem;
    }

    @keyframes gallery-fade-in {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .gallery-item-card {
            animation: none;
        }

        .gallery-lightbox,
        .gallery-lightbox-image,
        .gallery-lightbox-btn {
            transition: none;
        }
    }
</style>

<section
    class="relative h-[320px] overflow-hidden bg-cover bg-center bg-no-repeat text-white sm:h-[390px]"
    style="background-image: url('{{ $gallery->cover ? Storage::url($gallery->cover) : 'https://placehold.co/1800x1000' }}');"
>
    <div class="absolute inset-0 bg-slate-950/60"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/25 to-black/65"></div>

    <div class="relative mx-auto flex h-full max-w-7xl items-end px-4 pb-10 pt-20 sm:px-6 lg:px-8">
        <div>
            <a href="{{ route('galleries.index') }}" class="inline-flex rounded-full border border-white/35 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white transition hover:bg-white/20">
                &#8592; {{ __('Kembali ke Galeri') }}
            </a>
            <h1 class="mt-4 text-3xl font-black leading-tight sm:text-5xl">{{ $gallery->title }}</h1>
            <p class="mt-2 text-sm font-medium text-slate-100 sm:text-base">
                {{ __(':count foto dokumentasi', ['count' => $galleryItems->count()]) }}
            </p>
        </div>
    </div>
</section>

<section class="section-soft-separator bg-[#f4f5f7] pb-16 pt-10 sm:pb-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-slate-200/90 bg-gradient-to-br from-white via-slate-50 to-blue-50/35 p-5 shadow-[0_20px_45px_rgba(15,23,42,0.08)] sm:p-8">
            @if ($gallery->description)
                <p class="rounded-2xl border border-blue-100 bg-blue-50/60 px-5 py-4 text-sm leading-7 text-slate-700 sm:text-base">
                    {{ $gallery->description }}
                </p>
            @endif

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($galleryItems as $item)
                    @php
                        $rawItemImage = (string) data_get($item, 'image');
                        $imageUrl = \Illuminate\Support\Str::startsWith($rawItemImage, ['http://', 'https://'])
                            ? $rawItemImage
                            : Storage::url($rawItemImage);
                        $imageCaption = (string) (data_get($item, 'caption') ?: $gallery->title);
                    @endphp
                    <figure class="gallery-item-card group overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_16px_28px_rgba(37,99,235,0.16)]" style="animation-delay: {{ $loop->index * 70 }}ms;">
                        <button
                            type="button"
                            class="block w-full"
                            data-gallery-open
                            data-gallery-src="{{ $imageUrl }}"
                            data-gallery-caption="{{ $imageCaption }}"
                            aria-label="{{ __('Buka foto') }}"
                        >
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageCaption }}"
                                draggable="false"
                                class="h-44 w-full rounded-xl object-cover transition-transform duration-700 group-hover:scale-105 sm:h-52"
                            >
                        </button>
                        @if (filled(data_get($item, 'caption')))
                            <figcaption class="mt-3 line-clamp-2 text-xs font-medium text-slate-600 sm:text-sm">{{ data_get($item, 'caption') }}</figcaption>
                        @endif
                    </figure>
                @empty
                    <div class="col-span-full rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-slate-500">
                        {{ __('Belum ada foto dalam album ini.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<div class="gallery-lightbox" data-gallery-lightbox aria-hidden="true">
    <div class="gallery-lightbox-stage" data-gallery-stage>
        <div class="gallery-lightbox-toolbar">
            <button type="button" class="gallery-lightbox-btn" data-gallery-close aria-label="{{ __('Tutup') }}">X</button>
        </div>
        <img src="" alt="" class="gallery-lightbox-image" data-gallery-lightbox-image draggable="false">
        <p class="gallery-lightbox-caption" data-gallery-lightbox-caption></p>
    </div>
</div>

<script>
    (() => {
        const openTriggers = Array.from(document.querySelectorAll('[data-gallery-open]'));
        const modal = document.querySelector('[data-gallery-lightbox]');
        const stage = document.querySelector('[data-gallery-stage]');
        const image = document.querySelector('[data-gallery-lightbox-image]');
        const caption = document.querySelector('[data-gallery-lightbox-caption]');
        const closeButton = document.querySelector('[data-gallery-close]');
        const htmlRoot = document.documentElement;

        if (!openTriggers.length || !modal || !stage || !image || !caption || !closeButton) {
            return;
        }

        const wheelZoomIntensity = 0.0015;
        const minZoom = 1;
        const maxZoom = 4.6;
        const panSpeed = 1.16;
        let zoomLevel = 1;
        let offsetX = 0;
        let offsetY = 0;
        let isDragging = false;
        let activePointerId = null;
        let dragStartX = 0;
        let dragStartY = 0;
        let pinchStartDistance = null;
        let pinchStartZoom = minZoom;
        let isTouchPanning = false;
        let touchPanStartX = 0;
        let touchPanStartY = 0;
        let transformRafId = null;

        const clampZoom = (value) => Math.min(maxZoom, Math.max(minZoom, value));

        const updateCursor = () => {
            if (zoomLevel <= 1) {
                image.style.cursor = 'zoom-in';
                return;
            }

            image.style.cursor = isDragging ? 'grabbing' : 'grab';
        };

        const setDraggingState = (status) => {
            isDragging = status;
            image.classList.toggle('is-dragging', status);
            updateCursor();
        };

        const getImageFitSize = () => {
            const naturalWidth = image.naturalWidth || image.clientWidth || 1;
            const naturalHeight = image.naturalHeight || image.clientHeight || 1;
            const stageWidth = stage.clientWidth || 1;
            const stageHeight = stage.clientHeight || 1;
            const imageRatio = naturalWidth / naturalHeight;
            const stageRatio = stageWidth / stageHeight;

            if (imageRatio > stageRatio) {
                return {
                    width: stageWidth,
                    height: stageWidth / imageRatio,
                };
            }

            return {
                width: stageHeight * imageRatio,
                height: stageHeight,
            };
        };

        const clampOffsets = () => {
            if (zoomLevel <= 1) {
                offsetX = 0;
                offsetY = 0;
                return;
            }

            const fitSize = getImageFitSize();
            const scaledWidth = fitSize.width * zoomLevel;
            const scaledHeight = fitSize.height * zoomLevel;
            const overscrollX = Math.min(420, (stage.clientWidth * 0.32) + (zoomLevel - 1) * 76);
            const overscrollY = Math.min(360, (stage.clientHeight * 0.28) + (zoomLevel - 1) * 62);
            const maxOffsetX = Math.max(0, ((scaledWidth - stage.clientWidth) / 2) + overscrollX);
            const maxOffsetY = Math.max(0, ((scaledHeight - stage.clientHeight) / 2) + overscrollY);

            offsetX = Math.min(maxOffsetX, Math.max(-maxOffsetX, offsetX));
            offsetY = Math.min(maxOffsetY, Math.max(-maxOffsetY, offsetY));
        };

        const renderTransform = () => {
            clampOffsets();
            image.style.transform = `translate3d(${offsetX}px, ${offsetY}px, 0) scale(${zoomLevel})`;
            updateCursor();
        };

        const applyTransform = (immediate = false) => {
            if (immediate) {
                if (transformRafId !== null) {
                    window.cancelAnimationFrame(transformRafId);
                    transformRafId = null;
                }
                renderTransform();
                return;
            }

            if (transformRafId !== null) {
                return;
            }

            transformRafId = window.requestAnimationFrame(() => {
                transformRafId = null;
                renderTransform();
            });
        };

        const applyZoom = (nextZoom, clientX = null, clientY = null) => {
            const previousZoom = zoomLevel;
            zoomLevel = clampZoom(nextZoom);

            if (zoomLevel <= 1) {
                offsetX = 0;
                offsetY = 0;
                applyTransform(true);
                return;
            }

            if (clientX !== null && clientY !== null && previousZoom > 0) {
                const stageRect = stage.getBoundingClientRect();
                const focusX = clientX - stageRect.left - stageRect.width / 2;
                const focusY = clientY - stageRect.top - stageRect.height / 2;
                const zoomRatio = zoomLevel / previousZoom;

                offsetX = (offsetX - focusX) * zoomRatio + focusX;
                offsetY = (offsetY - focusY) * zoomRatio + focusY;
            }

            applyTransform();
        };

        const getTouchDistance = (touches) => {
            if (touches.length < 2) {
                return 0;
            }

            const dx = touches[0].clientX - touches[1].clientX;
            const dy = touches[0].clientY - touches[1].clientY;
            return Math.hypot(dx, dy);
        };

        const closeModal = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            htmlRoot.classList.remove('overflow-hidden');
            image.setAttribute('src', '');
            image.setAttribute('alt', '');
            caption.textContent = '';
            setDraggingState(false);
            activePointerId = null;
            pinchStartDistance = null;
            isTouchPanning = false;
            applyZoom(1);
        };

        const openModal = (src, altText, captionText) => {
            if (!src) {
                return;
            }

            image.setAttribute('src', src);
            image.setAttribute('alt', altText || '{{ __('Foto galeri') }}');
            caption.textContent = captionText || altText || '';
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            htmlRoot.classList.add('overflow-hidden');
            applyZoom(1);

            const updateViewWhenLoaded = () => applyTransform(true);
            if (image.complete) {
                updateViewWhenLoaded();
            } else {
                image.addEventListener('load', updateViewWhenLoaded, { once: true });
            }
        };

        openTriggers.forEach((trigger) => {
            trigger.addEventListener('click', () => {
                openModal(
                    trigger.getAttribute('data-gallery-src'),
                    trigger.getAttribute('data-gallery-caption'),
                    trigger.getAttribute('data-gallery-caption')
                );
            });
        });

        closeButton.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (!stage.contains(event.target)) {
                closeModal();
            }
        });

        stage.addEventListener('wheel', (event) => {
            event.preventDefault();
            const multiplier = Math.exp(-event.deltaY * wheelZoomIntensity);
            applyZoom(zoomLevel * multiplier, event.clientX, event.clientY);
        }, { passive: false });

        stage.addEventListener('pointerdown', (event) => {
            if (zoomLevel <= 1 || event.pointerType === 'touch') {
                return;
            }

            if (event.pointerType === 'mouse' && event.button !== 0) {
                return;
            }

            if (event.target.closest('[data-gallery-close]')) {
                return;
            }

            setDraggingState(true);
            activePointerId = event.pointerId;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            stage.setPointerCapture(event.pointerId);
        });

        stage.addEventListener('pointermove', (event) => {
            if (!isDragging || zoomLevel <= 1 || activePointerId !== event.pointerId) {
                return;
            }

            event.preventDefault();
            offsetX += (event.clientX - dragStartX) * panSpeed;
            offsetY += (event.clientY - dragStartY) * panSpeed;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            applyTransform(true);
        });

        const stopDrag = (event) => {
            if (!isDragging || (event.pointerId !== undefined && activePointerId !== event.pointerId)) {
                return;
            }

            setDraggingState(false);
            activePointerId = null;
            if (event.pointerId !== undefined && stage.hasPointerCapture(event.pointerId)) {
                stage.releasePointerCapture(event.pointerId);
            }
        };

        stage.addEventListener('pointerup', stopDrag);
        stage.addEventListener('pointercancel', stopDrag);
        stage.addEventListener('pointerleave', stopDrag);

        const preventNativeDrag = (event) => {
            event.preventDefault();
        };

        image.addEventListener('dragstart', preventNativeDrag);
        stage.addEventListener('dragstart', preventNativeDrag);

        stage.addEventListener('touchstart', (event) => {
            if (event.touches.length === 2) {
                pinchStartDistance = getTouchDistance(event.touches);
                pinchStartZoom = zoomLevel;
                isTouchPanning = false;
                return;
            }

            if (event.touches.length === 1 && zoomLevel > 1) {
                isTouchPanning = true;
                touchPanStartX = event.touches[0].clientX;
                touchPanStartY = event.touches[0].clientY;
            }
        }, { passive: false });

        stage.addEventListener('touchmove', (event) => {
            if (event.touches.length === 2 && pinchStartDistance !== null) {
                event.preventDefault();
                const currentDistance = getTouchDistance(event.touches);
                if (currentDistance <= 0) {
                    return;
                }

                const midpointX = (event.touches[0].clientX + event.touches[1].clientX) / 2;
                const midpointY = (event.touches[0].clientY + event.touches[1].clientY) / 2;
                applyZoom(pinchStartZoom * (currentDistance / pinchStartDistance), midpointX, midpointY);
                return;
            }

            if (event.touches.length === 1 && isTouchPanning && zoomLevel > 1) {
                event.preventDefault();
                const currentX = event.touches[0].clientX;
                const currentY = event.touches[0].clientY;
                offsetX += (currentX - touchPanStartX) * panSpeed;
                offsetY += (currentY - touchPanStartY) * panSpeed;
                touchPanStartX = currentX;
                touchPanStartY = currentY;
                applyTransform(true);
            }
        }, { passive: false });

        stage.addEventListener('touchend', (event) => {
            if (event.touches.length < 2) {
                pinchStartDistance = null;
            }

            if (event.touches.length === 1 && zoomLevel > 1) {
                isTouchPanning = true;
                touchPanStartX = event.touches[0].clientX;
                touchPanStartY = event.touches[0].clientY;
            } else if (event.touches.length === 0) {
                isTouchPanning = false;
            }
        });

        stage.addEventListener('touchcancel', () => {
            pinchStartDistance = null;
            isTouchPanning = false;
        });

        image.addEventListener('dblclick', (event) => {
            event.preventDefault();
            applyZoom(zoomLevel > 1 ? 1 : 2.4, event.clientX, event.clientY);
        });

        document.addEventListener('keydown', (event) => {
            if (!modal.classList.contains('is-open')) {
                return;
            }

            if (event.key === 'Escape') {
                closeModal();
            }
        });

        window.addEventListener('resize', () => {
            if (!modal.classList.contains('is-open')) {
                return;
            }

            applyTransform(true);
        });
    })();
</script>
@endsection
