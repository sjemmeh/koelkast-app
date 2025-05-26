<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request; // Belangrijk: importeer de Request klasse

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @param  \Illuminate\Http\Request  $request // Injecteer het Request object
     * @return void
     */
    public function boot(Request $request) // Nu heb je toegang tot $request
    {
        // Forceer HTTPS als:
        // 1. De APP_ENV 'production' is, OF
        // 2. De APP_URL in je .env begint met 'https://', OF
        // 3. De huidige browserverbinding via HTTPS loopt.
        if (
            $this->app->environment('production') ||
            str_starts_with(config('app.url'), 'https') ||
            $request->isSecure() // Controleert of de huidige verbinding HTTPS is
        ) {
            URL::forceScheme('https');
        }
        // Anders (niet productie, APP_URL is http://, en browserverbinding is http://)
        // worden URLs standaard met HTTP gegenereerd, passend bij de verbinding
        // en je APP_URL.
    }
}