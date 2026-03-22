<?php

namespace App\Filament\Resources\OfficialStructureTitleResource\Pages;

use App\Filament\Resources\OfficialStructureTitleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfficialStructureTitle extends EditRecord
{
    protected static string $resource = OfficialStructureTitleResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['additional_titles'] = \App\Models\OfficialStructureTitle::normalizeAdditionalTitles((array) ($data['additional_titles'] ?? []));

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
