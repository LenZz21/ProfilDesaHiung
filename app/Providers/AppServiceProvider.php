<?php

namespace App\Providers;

use App\Models\ComplaintSubmission;
use App\Models\LetterSubmission;
use App\Observers\ComplaintSubmissionObserver;
use App\Observers\LetterSubmissionObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Railway containers can start without pre-created storage framework folders.
        // Ensure temp/cache directories exist before Livewire handles uploads.
        foreach ([
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ] as $directory) {
            if (! File::isDirectory($directory)) {
                File::ensureDirectoryExists($directory, 0755, true);
            }
        }

        ComplaintSubmission::observe(ComplaintSubmissionObserver::class);
        LetterSubmission::observe(LetterSubmissionObserver::class);

        Livewire::component(
            'app.filament.widgets.page-header-settings-widget',
            \App\Filament\Widgets\PageHeaderSettingsWidget::class
        );

        Livewire::component(
            'app.filament.widgets.vision-mission-settings-widget',
            \App\Filament\Widgets\VisionMissionSettingsWidget::class
        );
    }
}
