<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\PageSetting;
use App\Models\VillageProfile;

class EventController extends Controller
{
    public function index()
    {
        $profile = VillageProfile::query()->first();
        $eventsPageSetting = PageSetting::resolve(PageSetting::PAGE_EVENTS);
        $baseQuery = Event::published();

        return view('events.index', [
            'profile' => $profile,
            'eventsPageSetting' => $eventsPageSetting,
            'events' => (clone $baseQuery)->orderBy('start_at')->paginate(9)->withQueryString(),
            'totalAgenda' => (clone $baseQuery)->count(),
            'upcomingAgenda' => (clone $baseQuery)->where('start_at', '>=', now())->count(),
            'locationCount' => (clone $baseQuery)->whereNotNull('location')->distinct('location')->count('location'),
            'seoTitle' => __('Agenda Desa'),
            'seoDescription' => __('Jadwal agenda dan kegiatan desa terdekat.'),
        ]);
    }

    public function show(Event $event)
    {
        abort_unless($event->status === 'published', 404);

        $profile = VillageProfile::query()->first();
        $relatedEvents = Event::published()
            ->whereKeyNot($event->id)
            ->orderBy('start_at')
            ->take(3)
            ->get();

        return view('events.show', [
            'event' => $event,
            'profile' => $profile,
            'relatedEvents' => $relatedEvents,
            'seoTitle' => $event->title,
            'seoDescription' => str($event->description)->limit(160)->toString(),
        ]);
    }
}
