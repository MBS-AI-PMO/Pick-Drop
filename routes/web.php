<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\SchoolRouteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.auth.login');
})->name('login'); // default landing redirects to login

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // PickDrop Domain Routes
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
    Route::resource('vehicles', \App\Http\Controllers\VehicleController::class)->except(['create', 'show', 'edit']);
    Route::resource('vehicle-categories', \App\Http\Controllers\VehicleCategoryController::class)->except(['create', 'show', 'edit']);
    Route::get('/locations/cities', [LocationController::class, 'citiesIndex'])->name('locations.cities.index');
    Route::get('/locations/areas', [LocationController::class, 'areasIndex'])->name('locations.areas.index');
    Route::post('/locations/cities', [LocationController::class, 'storeCity'])->name('locations.cities.store');
    Route::post('/locations/cities/import', [LocationController::class, 'importCities'])->name('locations.cities.import');
    Route::put('/locations/cities/{city}', [LocationController::class, 'updateCity'])->name('locations.cities.update');
    Route::delete('/locations/cities/{city}', [LocationController::class, 'destroyCity'])->name('locations.cities.destroy');
    Route::post('/locations/areas', [LocationController::class, 'storeArea'])->name('locations.areas.store');
    Route::put('/locations/areas/{area}', [LocationController::class, 'updateArea'])->name('locations.areas.update');
    Route::delete('/locations/areas/{area}', [LocationController::class, 'destroyArea'])->name('locations.areas.destroy');
    Route::get('/routes', [SchoolRouteController::class, 'index'])->name('routes.index');
    Route::get('/routes/create', [SchoolRouteController::class, 'create'])->name('routes.create');
    Route::post('/routes', [SchoolRouteController::class, 'store'])->name('routes.store');
    Route::get('/routes/{route}/edit', [SchoolRouteController::class, 'edit'])->name('routes.edit');
    Route::put('/routes/{route}', [SchoolRouteController::class, 'update'])->name('routes.update');
    Route::delete('/routes/{route}', [SchoolRouteController::class, 'destroy'])->name('routes.destroy');
    Route::get('/payments', function () { return view('pickdrop.payments.index'); })->name('payments.index');
    Route::get('/reports', function () { return view('pickdrop.reports.index'); })->name('reports.index');
});

// Auth Routes (Public)
Route::group(['prefix' => 'auth'], function(){
    Route::get('login', function () { return view('pages.auth.login'); })->name('auth.login');
    Route::get('register', function () { return view('pages.auth.register'); })->name('auth.register');
    Route::get('forgot-password', function () { return view('pages.auth.forgot-password'); })->name('auth.forgot-password');

    // Auth form submissions
    Route::post('login', [AuthController::class, 'login'])
        ->name('login'); // Keep name as 'login' for compatibility with auth middleware & redirects

    Route::post('register', [AuthController::class, 'register'])
        ->name('auth.register.submit');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password.submit');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
})->name('clear-cache');

// 404 for undefined routes
Route::any('/{page?}',function(){
    return View::make('pages.error.404');
})->where('page','.*');

