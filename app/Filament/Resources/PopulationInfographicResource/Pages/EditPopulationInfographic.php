<?php

namespace App\Filament\Resources\PopulationInfographicResource\Pages;

use App\Filament\Resources\PopulationInfographicResource;
use Filament\Resources\Pages\EditRecord;

class EditPopulationInfographic extends EditRecord
{
    protected static string $resource = PopulationInfographicResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
