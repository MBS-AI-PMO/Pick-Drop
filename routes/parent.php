<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentSelf\AuthController;
use App\Http\Controllers\Api\ParentSelf\ProfileController;
use App\Http\Controllers\Api\ParentSelf\StudentController;
use App\Http\Controllers\Api\ParentSelf\LocationController;
use App\Http\Controllers\Api\ParentSelf\RequestController;
use App\Http\Controllers\Api\ParentSelf\AccountController;
use App\Http\Controllers\Api\ParentSelf\TripController;
use App\Http\Controllers\Api\ParentSelf\IssueController;
use App\Http\Controllers\Api\ParentSelf\NotificationController;
use App\Http\Controllers\Api\ParentSelf\NotificationPreferenceController;
use App\Http\Controllers\Api\ParentSelf\MessageController;

/*
|--------------------------------------------------------------------------
| Parent Mobile App API Routes  (/api/parent/*)
| Self Mobile App API Routes    (/api/self/*)
|--------------------------------------------------------------------------
*/

// Shared endpoints between parent & self apps
$sharedAuthRoutes = function (string $scope) {
    Route::post('register', [AuthController::class, 'register'])->name("api.$scope.register");
    Route::post('login', [AuthController::class, 'login'])->name("api.$scope.login");
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name("api.$scope.forgot-password");
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name("api.$scope.reset-password");

    Route::middleware('auth:sanctum')->group(function () use ($scope) {
        Route::post('logout', [AuthController::class, 'logout'])->name("api.$scope.logout");

        // Email verify (token-based for now)
        Route::post('email/verify/send', [AuthController::class, 'sendEmailVerification'])->name("api.$scope.email.verify.send");
        Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name("api.$scope.email.verify");

        // Profile
        Route::get('me', [ProfileController::class, 'show'])->name("api.$scope.me.show");
        Route::put('me', [ProfileController::class, 'update'])->name("api.$scope.me.update");

        // Account settings
        Route::put('account/change-password', [AccountController::class, 'changePassword'])->name("api.$scope.account.change-password");
        Route::delete('account', [AccountController::class, 'deleteAccount'])->name("api.$scope.account.delete");

        // Locations
        Route::get('cities', [LocationController::class, 'cities'])->name("api.$scope.cities.index");
        Route::get('cities/{city}/areas', [LocationController::class, 'areas'])->name("api.$scope.cities.areas");

        // Pick‑Drop requests (trips)
        Route::get('requests', [RequestController::class, 'index'])->name("api.$scope.requests.index");
        Route::post('requests', [RequestController::class, 'store'])->name("api.$scope.requests.store");
        Route::get('requests/{requestId}', [RequestController::class, 'show'])->name("api.$scope.requests.show");
        Route::put('requests/{requestId}', [RequestController::class, 'update'])->name("api.$scope.requests.update");
        Route::delete('requests/{requestId}', [RequestController::class, 'cancel'])->name("api.$scope.requests.cancel");
        Route::get('requests/{requestId}/driver', [RequestController::class, 'driverInfo'])->name("api.$scope.requests.driver");
        Route::get('requests/{requestId}/tracking', [RequestController::class, 'tracking'])->name("api.$scope.requests.tracking");

        // Trips
        Route::get('trips/recent', [TripController::class, 'recent'])->name("api.$scope.trips.recent");
        Route::get('trips/today-status', [TripController::class, 'todayStatus'])->name("api.$scope.trips.today-status");

        // Issues
        Route::get('issues', [IssueController::class, 'index'])->name("api.$scope.issues.index");
        Route::post('issues', [IssueController::class, 'store'])->name("api.$scope.issues.store");

        // Notifications + preferences
        Route::get('notifications', [NotificationController::class, 'index'])->name("api.$scope.notifications.index");
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name("api.$scope.notifications.mark-all-read");
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name("api.$scope.notifications.read");

        Route::get('notification-preferences', [NotificationPreferenceController::class, 'show'])->name("api.$scope.notification-preferences.show");
        Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name("api.$scope.notification-preferences.update");

        // Messaging to driver (per trip)
        Route::get('requests/{pickupRequest}/messages', [MessageController::class, 'index'])->name("api.$scope.requests.messages.index");
        Route::post('requests/{pickupRequest}/messages', [MessageController::class, 'send'])->name("api.$scope.requests.messages.send");
    });
};

Route::prefix('parent')->group(function () use ($sharedAuthRoutes) {
    $sharedAuthRoutes('parent');

    // Children / Students (Parent app only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('api.parent.students.index');
        Route::post('students', [StudentController::class, 'store'])->name('api.parent.students.store');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('api.parent.students.show');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('api.parent.students.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('api.parent.students.destroy');
    });
});

Route::prefix('self')->group(function () use ($sharedAuthRoutes) {
    $sharedAuthRoutes('self');
});

