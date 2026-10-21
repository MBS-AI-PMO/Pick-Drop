# Pick-Drop performance booster (safe: no business-flow changes)
$ErrorActionPreference = "Continue"
Set-Location (Split-Path $PSScriptRoot -Parent)

Write-Host "==> Clearing & rebuilding Laravel caches..."
php artisan optimize:clear
php artisan config:cache
# Do NOT route:cache — XAMPP subdirectory (/Pick-Drop/public) breaks with cached routes (404 on /).
php artisan view:cache
php artisan event:cache

Write-Host "==> Optimizing Composer autoload..."
composer dump-autoload -o

if (-not (Test-Path "storage\framework\cache\data")) {
    New-Item -ItemType Directory -Path "storage\framework\cache\data" -Force | Out-Null
}
if (-not (Test-Path "storage\framework\sessions")) {
    New-Item -ItemType Directory -Path "storage\framework\sessions" -Force | Out-Null
}

Write-Host "==> Done. Restart Apache from XAMPP Control Panel so OPcache loads."
php artisan about
