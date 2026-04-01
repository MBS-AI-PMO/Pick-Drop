<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Driver\AuthController;
use App\Http\Controllers\Api\Driver\ProfileController;
use App\Http\Controllers\Api\Driver\ServiceAreaController;
use App\Http\Controllers\Api\Driver\RequestController;
use App\Http\Controllers\Api\Driver\RideController;
use App\Http\Controllers\Api\Driver\IssueController;

/*
|--------------------------------------------------------------------------
| Driver Mobile App API Routes
|--------------------------------------------------------------------------
|
| Prefix automatically ho jayega /api/driver (Laravel api.php prefix + group).
|
*/

Route::prefix('driver')->group(function () {

    // Auth
    Route::post('register', [AuthController::class, 'register'])->name('api.driver.register');
    Route::post('login',    [AuthController::class, 'login'])->name('api.driver.login');
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.driver.logout');

    Route::middleware('auth:sanctum')->group(function () {
        // Profile (driver apna profile update karega, vehicle admin handle karega)
        Route::get('me', [ProfileController::class, 'show'])->name('api.driver.me.show');
        Route::put('me', [ProfileController::class, 'update'])->name('api.driver.me.update');

        // Service areas (cities/areas jahan driver service deta hai)
        Route::get('service-areas',  [ServiceAreaController::class, 'index'])->name('api.driver.service-areas.index');
        Route::post('service-areas', [ServiceAreaController::class, 'sync'])->name('api.driver.service-areas.sync'); // body: { "area_ids": [1,2,3] }

        // Parent/Self requests
        Route::get('requests/available', [RequestController::class, 'available'])->name('api.driver.requests.available');
        Route::post('requests/{request}/accept', [RequestController::class, 'accept'])->name('api.driver.requests.accept');
        Route::post('requests/{request}/reject', [RequestController::class, 'reject'])->name('api.driver.requests.reject');

        // Assigned students / rides (daily pick-drop management)
        Route::get('rides/today',          [RideController::class, 'today'])->name('api.driver.rides.today');        // aaj ke sab students / stops
        Route::get('rides',                [RideController::class, 'index'])->name('api.driver.rides.index');        // history
        Route::post('rides/{ride}/pickup', [RideController::class, 'markPickup'])->name('api.driver.rides.pickup');  // single student pickup done
        Route::post('rides/{ride}/drop',   [RideController::class, 'markDrop'])->name('api.driver.rides.drop');      // single student drop done

        // Live tracking (location + status like on_the_way, picked_all, dropped_all)
        Route::post('location/update', [RideController::class, 'updateLocation'])->name('api.driver.location.update'); // lat,lng
        Route::post('status/update',   [RideController::class, 'updateStatus'])->name('api.driver.status.update');     // on_the_way, picked, dropped, etc.

        // Delay / issue reporting (driver reason submit karega)
        Route::post('issues',          [IssueController::class, 'store'])->name('api.driver.issues.store');        // body: { route_id, type, reason, eta_change, ... }
        Route::get('issues/today',     [IssueController::class, 'today'])->name('api.driver.issues.today');       // optional: aaj ki issues list
    });
});

