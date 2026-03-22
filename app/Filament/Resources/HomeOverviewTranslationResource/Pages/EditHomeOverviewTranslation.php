<?php

namespace App\Filament\Resources\HomeOverviewTranslationResource\Pages;

use App\Filament\Resources\HomeOverviewTranslationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeOverviewTranslation extends EditRecord
{
    protected static string $resource = HomeOverviewTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
