<?php

namespace App\Filament\Widgets;

use App\Models\PageSetting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class PageHeaderSettingsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static bool $isDiscovered = false;
    protected static bool $isLazy = false;

    protected static string $view = 'filament.widgets.page-header-settings-widget';

    protected int | string | array $columnSpan = 'full';

    public string $pageKey = 'beranda';

    public string $sectionHeading = 'Informasi Halaman';

    public string $saveButtonLabel = 'Simpan';

    public ?array $formData = [];

    public function mount(?string $pageKey = null, ?string $sectionHeading = null, ?string $saveButtonLabel = null): void
    {
        $resolvedPageKey = filled($pageKey) ? (string) $pageKey : 'beranda';

        if (! array_key_exists($resolvedPageKey, PageSetting::pageOptions())) {
            $resolvedPageKey = 'beranda';
        }

        $this->pageKey = $resolvedPageKey;

        if (filled($sectionHeading)) {
            $this->sectionHeading = (string) $sectionHeading;
        }

        if (filled($saveButtonLabel)) {
            $this->saveButtonLabel = (string) $saveButtonLabel;
        }

        $setting = $this->resolveSetting();

        $this->form->fill([
            'title' => $setting->title,
            'subtitle' => $setting->subtitle,
            'hero_image' => $setting->hero_image,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('subtitle')
                    ->label('Subjudul')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\FileUpload::make('hero_image')
                    ->label('Gambar Utama')
                    ->image()
                    ->disk(config('filesystems.default'))
                    ->directory('page-settings')
                    ->visibility('public')
                    ->imageEditor()
                    ->maxSize(4096)
                    ->helperText('Pilih gambar dari perangkat Anda. Maksimal 4MB.'),
            ])
            ->columns(2)
            ->statePath('formData');
    }

    public function save(): void
    {
        $setting = $this->resolveSetting();
        $data = $this->form->getState();

        $setting->update([
            'title' => trim((string) ($data['title'] ?? '')),
            'subtitle' => trim((string) ($data['subtitle'] ?? '')),
            'hero_image' => $data['hero_image'] ?? null,
        ]);

        Notification::make()
            ->title('Header halaman berhasil disimpan.')
            ->success()
            ->send();
    }

    private function resolveSetting(): PageSetting
    {
        $defaults = PageSetting::defaults()[$this->pageKey] ?? [];
        $fallbackTitle = PageSetting::pageOptions()[$this->pageKey] ?? 'Halaman';

        return PageSetting::query()->firstOrCreate(
            ['page_key' => $this->pageKey],
            [
                'title' => (string) ($defaults['title'] ?? $fallbackTitle),
                'subtitle' => (string) ($defaults['subtitle'] ?? ''),
                'hero_image' => $defaults['hero_image'] ?? null,
            ]
        );
    }
}
