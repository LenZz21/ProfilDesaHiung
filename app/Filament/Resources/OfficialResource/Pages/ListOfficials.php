<?php

namespace App\Filament\Resources\OfficialResource\Pages;

use App\Filament\Resources\OfficialResource;
use App\Filament\Widgets\PageHeaderSettingsWidget;
use App\Filament\Widgets\VisionMissionSettingsWidget;
use App\Models\OfficialStructureTitle;
use App\Models\PageSetting;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ListRecords;

class ListOfficials extends ListRecords
{
    protected static string $resource = OfficialResource::class;

    protected function getHeaderActions(): array
    {
        $getCurrentTitleData = function (): array {
            $record = OfficialStructureTitle::current();
            $defaults = OfficialStructureTitle::defaults();

            return [
                'leader_title' => $record?->leader_title ?: $defaults['leader_title'],
                'secretary_title' => $record?->secretary_title ?: $defaults['secretary_title'],
                'section_heads_title' => $record?->section_heads_title ?: $defaults['section_heads_title'],
                'kaur_title' => $record?->kaur_title ?: $defaults['kaur_title'],
                'head_lindongang_title' => $record?->head_lindongang_title ?: $defaults['head_lindongang_title'],
                'additional_titles' => OfficialStructureTitle::normalizeAdditionalTitles((array) ($record?->additional_titles ?? [])),
            ];
        };

        return [
            Actions\Action::make('manageStructureTitles')
                ->label('Judul Struktur')
                ->icon('heroicon-o-tag')
                ->modalHeading('Atur Judul Struktur')
                ->slideOver()
                ->fillForm($getCurrentTitleData())
                ->form([
                    Forms\Components\TextInput::make('leader_title')
                        ->label('Judul Kepala Desa')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('secretary_title')
                        ->label('Judul Sekretaris')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('section_heads_title')
                        ->label('Judul Kepala Seksi')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('kaur_title')
                        ->label('Judul Kaur')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('head_lindongang_title')
                        ->label('Judul Kepala Lindongang')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Repeater::make('additional_titles')
                        ->label('Judul Tambahan')
                        ->schema([
                            Forms\Components\Hidden::make('key'),
                            Forms\Components\TextInput::make('title')
                                ->label('Judul')
                                ->required()
                                ->maxLength(255),
                        ])
                        ->default([])
                        ->addActionLabel('Tambah Judul')
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->collapsible(),
                ])
                ->action(function (array $data): void {
                    $data['additional_titles'] = OfficialStructureTitle::normalizeAdditionalTitles((array) ($data['additional_titles'] ?? []));

                    $record = OfficialStructureTitle::current();

                    if ($record) {
                        $record->update($data);
                    } else {
                        OfficialStructureTitle::query()->create($data);
                    }
                }),
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PageHeaderSettingsWidget::make([
                'pageKey' => PageSetting::PAGE_PROFILE,
                'sectionHeading' => 'Header Profil Desa (Judul, Subjudul, Gambar)',
                'saveButtonLabel' => 'Simpan Header Profil',
            ]),
            VisionMissionSettingsWidget::make([
                'sectionHeading' => 'Visi & Misi Profil Desa',
                'saveButtonLabel' => 'Simpan Visi & Misi',
            ]),
        ];
    }
}
