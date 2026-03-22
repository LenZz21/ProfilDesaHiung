<?php

namespace App\Filament\Resources\PageSettingResource\Pages;

use App\Filament\Resources\PageSettingResource;
use App\Models\PageSetting;
use Filament\Resources\Pages\ListRecords;

class ListPageSettings extends ListRecords
{
    protected static string $resource = PageSettingResource::class;

    public function mount(): void
    {
        PageSetting::ensureDefaults();

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}

