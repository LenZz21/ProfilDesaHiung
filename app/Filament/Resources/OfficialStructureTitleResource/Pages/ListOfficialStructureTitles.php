<?php

namespace App\Filament\Resources\OfficialStructureTitleResource\Pages;

use App\Filament\Resources\OfficialStructureTitleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfficialStructureTitles extends ListRecords
{
    protected static string $resource = OfficialStructureTitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
