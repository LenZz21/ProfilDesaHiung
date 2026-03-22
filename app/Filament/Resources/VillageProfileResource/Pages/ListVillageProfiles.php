<?php

namespace App\Filament\Resources\VillageProfileResource\Pages;

use App\Filament\Resources\VillageProfileResource;
use App\Models\VillageProfile;
use Filament\Resources\Pages\ListRecords;

class ListVillageProfiles extends ListRecords
{
    protected static string $resource = VillageProfileResource::class;

    public function mount(): void
    {
        parent::mount();

        $record = VillageProfile::query()->firstOrCreate(['name' => 'Kampung Hiung']);

        $this->redirect(static::getResource()::getUrl('edit', ['record' => $record]));
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
