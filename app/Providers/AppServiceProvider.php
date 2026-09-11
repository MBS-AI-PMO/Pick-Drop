<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
        Paginator::useBootstrapFive();

        Mail::alwaysFrom((string) config('mail.from.address'), 'PickDrop');

        // ngrok/XAMPP often terminate TLS at the tunnel; force asset URLs
        // to match APP_URL so HTTPS pages do not load http:// CSS/JS.
        $appUrl = (string) config('app.url');
        if ($appUrl !== '') {
            URL::forceRootUrl($appUrl);
            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }
    }
}
