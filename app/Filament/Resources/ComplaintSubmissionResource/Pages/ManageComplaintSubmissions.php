<?php

namespace App\Filament\Resources\ComplaintSubmissionResource\Pages;

use App\Filament\Resources\ComplaintSubmissionResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Models\PageSetting;
use Filament\Resources\Pages\ManageRecords;

class ManageComplaintSubmissions extends ManageRecords
{
    protected static string $resource = ComplaintSubmissionResource::class;

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
