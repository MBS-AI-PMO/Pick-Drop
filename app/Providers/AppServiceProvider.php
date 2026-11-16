<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        // Tunnels (ngrok, Cloudflare) terminate TLS in front of Apache, and the
        // public hostname changes whenever the tunnel restarts. APP_URL is often
        // still the previous tunnel, which makes every stylesheet, script, and
        // uploaded image 404 or hit a dead host. For web requests, build URLs
        // from the host the client actually used, and honor X-Forwarded-Proto so
        // an HTTPS page does not request http:// assets. APP_URL is still used
        // for CLI/mail. The public disk URL must follow the same root: Storage
        // URLs are not affected by URL::forceRootUrl on their own.
        $root = $this->publicRootUrl();
        if ($root !== '') {
            URL::forceRootUrl($root);
            if (str_starts_with($root, 'https://')) {
                URL::forceScheme('https');
            }

            config(['filesystems.disks.public.url' => $root.'/storage']);
            Storage::forgetDisk('public');
        }
    }

    /**
     * Host the current client used, including the /Pick-Drop/public base path.
     */
    protected function publicRootUrl(): string
    {
        if (! $this->app->runningInConsole() && request()->getHttpHost() !== '') {
            $request = request();
            $forwardedProto = strtolower(trim(explode(',', (string) $request->headers->get('x-forwarded-proto', ''))[0]));
            $scheme = in_array($forwardedProto, ['http', 'https'], true)
                ? $forwardedProto
                : $request->getScheme();
            $forwardedHost = trim(explode(',', (string) $request->headers->get('x-forwarded-host', ''))[0]);
            $host = $forwardedHost !== '' ? $forwardedHost : $request->getHttpHost();

            return $scheme.'://'.$host.$request->getBaseUrl();
        }

        return rtrim((string) config('app.url'), '/');
    }
}
