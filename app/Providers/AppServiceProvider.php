<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Voeg deze use statement toe bovenaan het bestand

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
        // Forceer HTTPS als de APP_URL met https begint (wat bij jou zo is)
        // of als de app in productie draait.
        if (str_starts_with(config('app.url'), 'https') || $this->app->environment('production')) {
            // URL::forceScheme('https');
        }
        // Je kunt ook simpelweg altijd forceren als je reverse proxy altijd SSL doet:
        // URL::forceScheme('https');
    }
}