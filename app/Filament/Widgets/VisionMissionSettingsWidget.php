<?php

namespace App\Filament\Widgets;

use App\Models\VillageProfile;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class VisionMissionSettingsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static bool $isDiscovered = false;
    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.vision-mission-settings-widget';

    protected int | string | array $columnSpan = 'full';

    public string $sectionHeading = 'Visi & Misi';

    public string $saveButtonLabel = 'Simpan Visi & Misi';

    public ?array $formData = [];

    public function mount(?string $sectionHeading = null, ?string $saveButtonLabel = null): void
    {
        if (filled($sectionHeading)) {
            $this->sectionHeading = (string) $sectionHeading;
        }

        if (filled($saveButtonLabel)) {
            $this->saveButtonLabel = (string) $saveButtonLabel;
        }

        $profile = $this->resolveProfile();

        $this->form->fill([
            'vision' => $profile->vision,
            'mission' => $profile->mission,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('vision')
                    ->label('Visi')
                    ->rows(4),
                Forms\Components\Textarea::make('mission')
                    ->label('Misi')
                    ->rows(6)
                    ->helperText('Gunakan baris baru untuk setiap poin misi agar tampil sebagai daftar bernomor di halaman profil.'),
            ])
            ->columns(2)
            ->statePath('formData');
    }

    public function save(): void
    {
        $profile = $this->resolveProfile();
        $data = $this->form->getState();

        $profile->update([
            'vision' => trim((string) ($data['vision'] ?? '')),
            'mission' => trim((string) ($data['mission'] ?? '')),
        ]);

        Notification::make()
            ->title('Visi & misi berhasil disimpan.')
            ->success()
            ->send();
    }

    private function resolveProfile(): VillageProfile
    {
        $profile = VillageProfile::query()->first();

        if ($profile) {
            return $profile;
        }

        return VillageProfile::query()->create([
            'name' => 'Kampung Hiung',
        ]);
    }
}
