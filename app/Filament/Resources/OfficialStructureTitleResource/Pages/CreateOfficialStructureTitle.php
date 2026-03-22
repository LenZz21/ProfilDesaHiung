<?php

namespace App\Filament\Resources\OfficialStructureTitleResource\Pages;

use App\Filament\Resources\OfficialStructureTitleResource;
use App\Models\OfficialStructureTitle;
use Filament\Resources\Pages\CreateRecord;

class CreateOfficialStructureTitle extends CreateRecord
{
    protected static string $resource = OfficialStructureTitleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = array_merge(OfficialStructureTitle::defaults(), $data);
        $data['additional_titles'] = OfficialStructureTitle::normalizeAdditionalTitles((array) ($data['additional_titles'] ?? []));

        return $data;
    }
}
