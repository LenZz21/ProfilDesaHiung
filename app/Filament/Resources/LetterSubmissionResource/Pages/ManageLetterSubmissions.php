<?php

namespace App\Filament\Resources\LetterSubmissionResource\Pages;

use App\Filament\Resources\LetterSubmissionResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Models\PageSetting;
use Filament\Resources\Pages\ManageRecords;

class ManageLetterSubmissions extends ManageRecords
{
    protected static string $resource = LetterSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PageHeaderSettingsWidget::make([
                'pageKey' => PageSetting::PAGE_SERVICES,
                'sectionHeading' => 'Informasi Halaman Layanan',
                'saveButtonLabel' => 'Simpan Header Layanan',
            ]),
        ];
    }
}
