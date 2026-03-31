<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentSelf\AuthController;
use App\Http\Controllers\Api\ParentSelf\ProfileController;
use App\Http\Controllers\Api\ParentSelf\StudentController;
use App\Http\Controllers\Api\ParentSelf\LocationController;
use App\Http\Controllers\Api\ParentSelf\RequestController;

/*
|--------------------------------------------------------------------------
| Parent / Self Mobile App API Routes
|--------------------------------------------------------------------------
|
| Prefix automatically ho jayega /api/parent-self (Laravel api.php prefix + group).
|
*/

Route::prefix('parent-self')->group(function () {

    // Auth (parent ya self user)
    Route::post('register', [AuthController::class, 'register']);   // name, phone, password, type=parent/self
    Route::post('login',    [AuthController::class, 'login']);
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {

        // Profile
        Route::get('me', [ProfileController::class, 'show']);
        Route::put('me', [ProfileController::class, 'update']);

        // Students (sirf jab type = parent ho)
        Route::get('students',              [StudentController::class, 'index']);
        Route::post('students',             [StudentController::class, 'store']);
        Route::get('students/{student}',    [StudentController::class, 'show']);
        Route::put('students/{student}',    [StudentController::class, 'update']);
        Route::delete('students/{student}', [StudentController::class, 'destroy']);

        // Cities / Areas dropdowns
        Route::get('cities',              [LocationController::class, 'cities']);
        Route::get('cities/{city}/areas', [LocationController::class, 'areas']);

        // Pick‑Drop requests
        Route::get('requests',              [RequestController::class, 'index']);
        Route::post('requests',             [RequestController::class, 'store']);
        Route::get('requests/{request}',    [RequestController::class, 'show']);
        Route::put('requests/{request}',    [RequestController::class, 'update']);  // until accepted
        Route::delete('requests/{request}', [RequestController::class, 'cancel']);

        // Driver / tracking info (after accept)
        Route::get('requests/{request}/driver',   [RequestController::class, 'driverInfo']);
        Route::get('requests/{request}/tracking', [RequestController::class, 'tracking']);
    });
});

