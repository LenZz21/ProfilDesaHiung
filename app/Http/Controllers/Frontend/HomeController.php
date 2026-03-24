<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Gallery;
use App\Models\HomeOverviewTranslation;
use App\Models\Official;
use App\Models\PageSetting;
use App\Models\PopulationInfographic;
use App\Models\Post;
use App\Models\VillageProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function __invoke()
    {
        $freshCutoff = now()->subDays(3);
        $resolveMediaUrl = static function (?string $rawPath): ?string {
            $path = trim((string) $rawPath);
            if ($path === '') {
                return null;
            }

            if (Str::startsWith(strtolower($path), ['http://', 'https://', 'data:image'])) {
                return $path;
            }

            return Storage::url($path);
        };
        $mapFreshPost = fn (Post $post) => [
            'type' => 'post',
            'type_label' => __('Berita'),
            'title' => (string) $post->title,
            'summary' => Str::limit((string) ($post->excerpt ?: strip_tags((string) $post->content)), 120),
            'url' => route('posts.show', $post),
            'created_at' => $post->created_at,
            'image_url' => $resolveMediaUrl($post->thumbnail)
                ?: 'https://placehold.co/640x360/e2e8f0/334155?text=Berita',
        ];
        $mapFreshEvent = fn (Event $event) => [
            'type' => 'event',
            'type_label' => __('Agenda'),
            'title' => (string) $event->title,
            'summary' => Str::limit((string) strip_tags((string) $event->description), 120),
            'url' => route('events.show', $event),
            'created_at' => $event->created_at,
            'image_url' => $resolveMediaUrl($event->banner)
                ?: 'https://placehold.co/640x360/ede9fe/4c1d95?text=Agenda',
        ];
        $mapFreshGallery = function (Gallery $gallery) use ($resolveMediaUrl) {
            $firstItemImage = (string) data_get($gallery->sortedItems()->first(), 'image', '');
            $coverSource = trim((string) ($gallery->cover ?: $firstItemImage));

            return [
                'type' => 'gallery',
                'type_label' => __('Galeri'),
                'title' => (string) ($gallery->title ?: __('Album Kegiatan')),
                'summary' => Str::limit((string) ($gallery->description ?: __('Dokumentasi terbaru kegiatan kampung.')), 120),
                'url' => route('galleries.show', $gallery),
                'created_at' => $gallery->created_at,
                'image_url' => $resolveMediaUrl($coverSource)
                    ?: 'https://placehold.co/640x360/dcfce7/14532d?text=Galeri',
            ];
        };
        $profile = VillageProfile::query()->first();
        $locale = strtolower((string) app()->getLocale());
        $isEnglishLocale = str_starts_with($locale, 'en');
        $homeOverviewEnglish = trim((string) (HomeOverviewTranslation::aboutForLocale('en') ?? ''));
        $resolvedHomeOverview = $isEnglishLocale && $homeOverviewEnglish !== ''
            ? $homeOverviewEnglish
            : trim((string) ($profile?->about ?? ''));
        $homePageSetting = PageSetting::resolve(PageSetting::PAGE_HOME);
        $latestPosts = Post::published()->latest('published_at')->take(5)->get();
        $freshPosts = Post::published()
            ->where('created_at', '>=', $freshCutoff)
            ->latest('created_at')
            ->take(9)
            ->get()
            ->map($mapFreshPost);
        $infographic = PopulationInfographic::query()
            ->with('summaryStats')
            ->latest('updated_at')
            ->first();
        $summaryStats = collect(
            $infographic?->summaryStats
                ?->map(fn ($item) => [
                    'label' => $item->label,
                    'value' => $item->value,
                    'color' => $item->color,
                ])
                ->values()
                ->all()
        );

        if ($summaryStats->isEmpty()) {
            $summaryStats = collect($infographic?->summary_stats ?: PopulationInfographic::defaultSummaryStats());
        }
        $preferredStatLabels = [
            'Total Penduduk',
            'Total Population',
            'Kepala Keluarga',
            'Family Heads',
            'Perempuan',
            'Female',
            'Laki-laki',
            'Male',
        ];
        $orderedStats = collect($preferredStatLabels)
            ->map(fn (string $label) => $summaryStats->first(fn (array $item) => data_get($item, 'label') === $label))
            ->filter();
        $fallbackStats = $summaryStats->reject(
            fn (array $item) => $orderedStats->contains(
                fn (array $selected) => data_get($selected, 'label') === data_get($item, 'label')
            )
        );
        $populationStats = $orderedStats
            ->concat($fallbackStats)
            ->take(4)
            ->map(fn (array $item) => [
                'label' => data_get($item, 'label', '-'),
                'value' => data_get($item, 'value', 0),
            ])
            ->values();
        $officials = Official::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->take(8)
            ->get();
        $resolveStructureGroup = fn (Official $official): string => $official->structure_group
            ?: Official::detectStructureGroupFromPosition($official->position);
        $featuredOfficial = $officials->first(
            fn (Official $official) => $resolveStructureGroup($official) === Official::GROUP_LEADER
        ) ?? $officials->first();
        $secretaryOfficial = $officials->first(
            fn (Official $official) => $official->id !== $featuredOfficial?->id
                && $resolveStructureGroup($official) === Official::GROUP_SECRETARY
        );
        $excludedOfficialIds = collect([$featuredOfficial?->id, $secretaryOfficial?->id])
            ->filter()
            ->values()
            ->all();
        $structureOfficials = collect([$featuredOfficial, $secretaryOfficial])
            ->filter()
            ->concat($officials->reject(fn (Official $official) => in_array($official->id, $excludedOfficialIds, true)))
            ->take(4)
            ->values();
        $galleryItems = Gallery::query()
            ->latest()
            ->get()
            ->flatMap(fn (Gallery $gallery) => $gallery->sortedItems()
                ->map(fn (array $item) => (object) [
                    'image' => (string) data_get($item, 'image', ''),
                    'caption' => (string) data_get($item, 'caption', ''),
                    'album_title' => (string) ($gallery->title ?? ''),
                    'created_at' => $gallery->created_at,
                ]))
            ->filter(fn (object $item) => $item->image !== '')
            ->take(6)
            ->values();
        $eventStatusPriority = static fn (Event $event): int => match ($event->agendaStatus()) {
            Event::AGENDA_STATUS_ONGOING => 0,
            Event::AGENDA_STATUS_UPCOMING => 1,
            default => 2,
        };
        $publishedEvents = Event::published()->orderBy('start_at')->get();
        $sortEventsByPriority = static fn (Event $left, Event $right): int => match ([$left->agendaStatus(), $right->agendaStatus()]) {
            [Event::AGENDA_STATUS_ONGOING, Event::AGENDA_STATUS_ONGOING] => ($right->start_at?->getTimestamp() ?? PHP_INT_MIN) <=> ($left->start_at?->getTimestamp() ?? PHP_INT_MIN),
            [Event::AGENDA_STATUS_UPCOMING, Event::AGENDA_STATUS_UPCOMING] => ($left->start_at?->getTimestamp() ?? PHP_INT_MAX) <=> ($right->start_at?->getTimestamp() ?? PHP_INT_MAX),
            [Event::AGENDA_STATUS_FINISHED, Event::AGENDA_STATUS_FINISHED] => (($right->end_at ?? $right->start_at)?->getTimestamp() ?? PHP_INT_MIN) <=> (($left->end_at ?? $left->start_at)?->getTimestamp() ?? PHP_INT_MIN),
            default => $eventStatusPriority($left) <=> $eventStatusPriority($right),
        };
        $prioritizedByStatus = collect([
            $publishedEvents
                ->filter(fn (Event $event) => $event->agendaStatus() === Event::AGENDA_STATUS_ONGOING)
                ->sort(fn (Event $left, Event $right) => $sortEventsByPriority($left, $right))
                ->first(),
            $publishedEvents
                ->filter(fn (Event $event) => $event->agendaStatus() === Event::AGENDA_STATUS_UPCOMING)
                ->sort(fn (Event $left, Event $right) => $sortEventsByPriority($left, $right))
                ->first(),
            $publishedEvents
                ->filter(fn (Event $event) => $event->agendaStatus() === Event::AGENDA_STATUS_FINISHED)
                ->sort(fn (Event $left, Event $right) => $sortEventsByPriority($left, $right))
                ->first(),
        ])
            ->filter()
            ->values();
        $fallbackEvents = $publishedEvents
            ->reject(fn (Event $event) => $prioritizedByStatus->contains(fn (Event $picked) => $picked->id === $event->id))
            ->sort(fn (Event $left, Event $right) => $sortEventsByPriority($left, $right))
            ->take(max(0, 3 - $prioritizedByStatus->count()));
        $nearestEvents = $prioritizedByStatus
            ->concat($fallbackEvents)
            ->take(3)
            ->values();
        $freshEvents = Event::published()
            ->where('created_at', '>=', $freshCutoff)
            ->latest('created_at')
            ->take(9)
            ->get()
            ->map($mapFreshEvent);
        $freshGalleries = Gallery::query()
            ->where('created_at', '>=', $freshCutoff)
            ->latest('created_at')
            ->take(9)
            ->get()
            ->map($mapFreshGallery);
        $freshHighlights = $freshPosts
            ->concat($freshEvents)
            ->concat($freshGalleries)
            ->sortByDesc(fn (array $item) => data_get($item, 'created_at')?->getTimestamp() ?? 0)
            ->take(9)
            ->values();
        $popupHighlights = $freshHighlights;
        $freshPopupVersion = $popupHighlights->isNotEmpty()
            ? ($popupHighlights->first()['created_at']?->format('YmdHis') ?? now()->format('YmdHis')).'-'.$popupHighlights->count()
            : 'empty';

        return view('home.index', [
            'profile' => $profile,
            'resolvedHomeOverview' => $resolvedHomeOverview,
            'homePageSetting' => $homePageSetting,
            'featuredOfficial' => $featuredOfficial,
            'structureOfficials' => $structureOfficials,
            'latestPosts' => $latestPosts,
            'freshHighlights' => $freshHighlights,
            'popupHighlights' => $popupHighlights,
            'freshPopupVersion' => $freshPopupVersion,
            'featuredPosts' => $latestPosts->take(2),
            'otherPosts' => $latestPosts->slice(2, 3),
            'nearestEvents' => $nearestEvents,
            'galleryItems' => $galleryItems,
            'populationStats' => $populationStats,
            'seoTitle' => __('Beranda Desa :name', ['name' => $profile?->name ?? '']),
            'seoDescription' => __('Website resmi profil desa, berita, agenda, galeri, publikasi, dan kontak.'),
        ]);
    }
}
