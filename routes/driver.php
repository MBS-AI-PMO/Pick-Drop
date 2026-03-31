<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Driver\AuthController;
use App\Http\Controllers\Api\Driver\ProfileController;
use App\Http\Controllers\Api\Driver\VehicleController;
use App\Http\Controllers\Api\Driver\ServiceAreaController;
use App\Http\Controllers\Api\Driver\RequestController;
use App\Http\Controllers\Api\Driver\RideController;

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
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        // Profile
        Route::get('me', [ProfileController::class, 'show']);
        Route::put('me', [ProfileController::class, 'update']);

        // Vehicle
        Route::get('vehicle',  [VehicleController::class, 'show']);
        Route::post('vehicle', [VehicleController::class, 'store']);
        Route::put('vehicle',  [VehicleController::class, 'update']);

        // Service areas (cities/areas jahan driver service deta hai)
        Route::get('service-areas',  [ServiceAreaController::class, 'index']);
        Route::post('service-areas', [ServiceAreaController::class, 'sync']); // body: { "area_ids": [1,2,3] }

        // Parent/Self requests
        Route::get('requests/available', [RequestController::class, 'available']);
        Route::post('requests/{request}/accept', [RequestController::class, 'accept']);
        Route::post('requests/{request}/reject', [RequestController::class, 'reject']);

        // Assigned students / rides
        Route::get('rides/today', [RideController::class, 'today']);
        Route::get('rides',       [RideController::class, 'index']);

        // Live tracking
        Route::post('location/update', [RideController::class, 'updateLocation']); // lat,lng
        Route::post('status/update',   [RideController::class, 'updateStatus']);   // on_the_way, picked, dropped, etc.
    });
});

