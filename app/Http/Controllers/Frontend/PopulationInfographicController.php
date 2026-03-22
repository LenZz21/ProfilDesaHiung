<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PopulationInfographic;
use App\Models\VillageProfile;

class PopulationInfographicController extends Controller
{
    public function index()
    {
        $isEnglish = app()->getLocale() === 'en';
        $profile = VillageProfile::query()->first();
        $infographic = PopulationInfographic::query()
            ->with([
                'summaryStats',
                'chartSections.items',
            ])
            ->latest('updated_at')
            ->first();

        $summaryStats = $infographic?->summaryStats
            ?->map(fn ($item) => [
                'label' => $isEnglish && filled($item->label_en)
                    ? $item->label_en
                    : $item->label,
                'value' => $item->value,
                'color' => $item->color,
            ])
            ->values()
            ->all();

        $chartSections = $infographic?->chartSections
            ?->map(fn ($section) => [
                'title' => $isEnglish && filled($section->title_en)
                    ? $section->title_en
                    : $section->title,
                'type' => $section->type,
                'items' => $section->items
                    ->map(fn ($item) => [
                        'label' => $isEnglish && filled($item->label_en)
                            ? $item->label_en
                            : $item->label,
                        'value' => $item->value,
                        'color' => $item->color,
                    ])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();

        if (blank($summaryStats)) {
            $summaryStats = $infographic?->summary_stats ?: PopulationInfographic::defaultSummaryStats();
        }

        if (blank($chartSections)) {
            $chartSections = $infographic?->chart_sections ?: PopulationInfographic::defaultChartSections();
        }

        $pageTitle = $isEnglish && filled($infographic?->title_en)
            ? (string) $infographic->title_en
            : ((string) ($infographic?->title ?: __('Infografis Penduduk')));

        $pageSubtitle = $isEnglish && filled($infographic?->subtitle_en)
            ? (string) $infographic->subtitle_en
            : ((string) ($infographic?->subtitle ?: __('Statistik kependudukan kampung yang terintegrasi dan transparan.')));

        return view('infographics.index', [
            'profile' => $profile,
            'infographic' => $infographic,
            'summaryStats' => $summaryStats,
            'chartSections' => $chartSections,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'heroImage' => $infographic?->hero_image_url ?? PopulationInfographic::defaultHeroImage(),
            'seoTitle' => $pageTitle,
            'seoDescription' => $pageSubtitle,
        ]);
    }
}
