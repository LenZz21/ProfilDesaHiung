<?php

namespace App\Filament\Resources\GalleryResource\Pages;

use App\Filament\Resources\GalleryResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Models\PageSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGalleries extends ListRecords
{
    protected static string $resource = GalleryResource::class;

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
                'pageKey' => PageSetting::PAGE_GALLERIES,
                'sectionHeading' => 'Informasi Halaman Galeri',
                'saveButtonLabel' => 'Simpan Header Galeri',
            ]),
        ];
    }
}
