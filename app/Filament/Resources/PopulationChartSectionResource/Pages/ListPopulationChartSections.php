<?php

namespace App\Filament\Resources\PopulationChartSectionResource\Pages;

use App\Filament\Resources\PopulationChartSectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPopulationChartSections extends ListRecords
{
    protected static string $resource = PopulationChartSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
