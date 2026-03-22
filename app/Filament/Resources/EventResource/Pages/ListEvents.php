<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Models\PageSetting;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

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
                'pageKey' => PageSetting::PAGE_EVENTS,
                'sectionHeading' => 'Informasi Halaman Agenda',
                'saveButtonLabel' => 'Simpan Header Agenda',
            ]),
        ];
    }
}
