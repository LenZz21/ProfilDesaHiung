<?php

namespace App\Filament\Resources\HomeOverviewTranslationResource\Pages;

use App\Filament\Resources\HomeOverviewTranslationResource;
use App\Models\HomeOverviewTranslation;
use Filament\Resources\Pages\ListRecords;

class ListHomeOverviewTranslations extends ListRecords
{
    protected static string $resource = HomeOverviewTranslationResource::class;

    public function mount(): void
    {
        parent::mount();

        HomeOverviewTranslation::query()->firstOrCreate(
            ['locale' => 'en'],
            ['about' => null]
        );
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
