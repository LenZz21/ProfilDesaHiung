<?php

namespace App\Providers;

use App\Models\ComplaintSubmission;
use App\Models\LetterSubmission;
use App\Observers\ComplaintSubmissionObserver;
use App\Observers\LetterSubmissionObserver;
use Illuminate\Support\ServiceProvider;
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
