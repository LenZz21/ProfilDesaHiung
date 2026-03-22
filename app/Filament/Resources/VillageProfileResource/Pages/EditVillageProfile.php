<?php

namespace App\Filament\Resources\VillageProfileResource\Pages;

use App\Filament\Resources\VillageProfileResource;
use App\Models\HomeOverviewTranslation;
use Filament\Resources\Pages\EditRecord;

class EditVillageProfile extends EditRecord
{
    protected static string $resource = VillageProfileResource::class;

    protected ?string $aboutEnDraft = null;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['about_en'] = (string) (HomeOverviewTranslation::aboutForLocale('en') ?? '');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->aboutEnDraft = trim((string) ($data['about_en'] ?? ''));

        unset($data['about_en']);

        return $data;
    }

    protected function afterSave(): void
    {
        $aboutEn = $this->aboutEnDraft ?? '';

        HomeOverviewTranslation::query()->updateOrCreate(
            ['locale' => 'en'],
            ['about' => $aboutEn !== '' ? $aboutEn : null]
        );

        $this->aboutEnDraft = null;
    }
}
