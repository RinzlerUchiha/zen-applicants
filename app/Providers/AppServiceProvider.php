<?php

namespace App\Providers;

use App\Services\ApplicationCompletenessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // The Application Form rail needs completeness on every page that
        // renders it. Computing it here keeps all 19 existing page views free
        // of the concern — none of them had to be edited for the redesign.
        // Only the Application Form chrome reads it (progress and the stepper).
        // layouts.layout no longer has a rail, so Documents does not pay for it.
        // Bound to the sidebar partial itself, so every page that includes it
        // — the Application Form sections, Documents, Assessments — gets the
        // same data (HireFlow 2.5 · M3).
        View::composer(['layouts.partials.form-rail', 'layouts.form-section'], function ($view) {
            if (!Auth::check()) {
                return;
            }

            $completeness = ApplicationCompletenessService::for(Auth::user());

            $view->with([
                'formSections' => $completeness->sections(),
                'formPercent'  => $completeness->percentage(),
                'formCounts'   => $completeness->blockingCounts(),
            ]);
        });
    }
}
