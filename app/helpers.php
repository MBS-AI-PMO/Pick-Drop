<?php
  
function active_class($path, $active = 'active') {
    return request()->is(...(array)$path) ? $active : '';
}

function is_active_route($path) {
    return request()->is(...(array)$path) ? 'true' : 'false';
}

function show_class($path) {
    return request()->is(...(array)$path) ? 'show' : '';
}

/**
 * Institutions stay in the database and APIs. Set this to true to show them again.
 */
function institutions_visible(): bool
{
    return false;
}

/**
 * Absolute URL for a file on the public disk.
 *
 * Uses the current request host (see AppServiceProvider), so mobile apps
 * are not sent links from a stale APP_URL / tunnel hostname.
 */
function public_storage_url(?string $path): ?string
{
    if ($path === null) {
        return null;
    }

    $path = str_replace('\\', '/', trim($path));

    if ($path === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $path) === 1) {
        return $path;
    }

    $path = ltrim($path, '/');

    if (str_starts_with($path, 'storage/')) {
        $path = substr($path, strlen('storage/'));
    }

    return url('storage/'.$path);
}