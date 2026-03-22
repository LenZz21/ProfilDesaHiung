<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Models\PageSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PageHeaderSettingsWidget::make([
                'pageKey' => PageSetting::PAGE_DOCUMENTS,
                'sectionHeading' => 'Informasi Halaman Publikasi',
                'saveButtonLabel' => 'Simpan Header Publikasi',
            ]),
        ];
    }
}
