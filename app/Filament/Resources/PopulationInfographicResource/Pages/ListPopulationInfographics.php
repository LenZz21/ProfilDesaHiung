<?php

namespace App\Filament\Resources\PopulationInfographicResource\Pages;

use App\Filament\Resources\PopulationInfographicResource;
use App\Models\PopulationInfographic;
use Filament\Resources\Pages\ListRecords;

class ListPopulationInfographics extends ListRecords
{
    protected static string $resource = PopulationInfographicResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = PopulationInfographic::query()->first();

        if (! $record) {
            $record = PopulationInfographic::query()->create([
                'title' => 'Infografis Penduduk',
                'subtitle' => null,
                'hero_image' => null,
            ]);
        }

        $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
