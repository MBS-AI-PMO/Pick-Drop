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

Route::prefix('parent')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.parent.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.parent.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.parent.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.parent.reset-password');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.parent.logout');

        // Email verify (token-based for now)
        Route::post('email/verify/send', [AuthController::class, 'sendEmailVerification'])->name('api.parent.email.verify.send');
        Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name('api.parent.email.verify');

        // Profile
        Route::get('me', [ProfileController::class, 'show'])->name('api.parent.me.show');
        Route::put('me', [ProfileController::class, 'update'])->name('api.parent.me.update');

        // Account settings
        Route::put('account/change-password', [AccountController::class, 'changePassword'])->name('api.parent.account.change-password');
        Route::delete('account', [AccountController::class, 'deleteAccount'])->name('api.parent.account.delete');

        // Locations
        Route::get('cities', [LocationController::class, 'cities'])->name('api.parent.cities.index');
        Route::get('cities/{city}/areas', [LocationController::class, 'areas'])->name('api.parent.cities.areas');

        // Pick-drop requests (trips)
        Route::get('requests', [RequestController::class, 'index'])->name('api.parent.requests.index');
        Route::post('requests', [RequestController::class, 'store'])->name('api.parent.requests.store');
        Route::get('requests/{requestId}', [RequestController::class, 'show'])->name('api.parent.requests.show');
        Route::put('requests/{requestId}', [RequestController::class, 'update'])->name('api.parent.requests.update');
        Route::delete('requests/{requestId}', [RequestController::class, 'cancel'])->name('api.parent.requests.cancel');
        Route::get('requests/{requestId}/driver', [RequestController::class, 'driverInfo'])->name('api.parent.requests.driver');
        Route::get('requests/{requestId}/tracking', [RequestController::class, 'tracking'])->name('api.parent.requests.tracking');

        // Trips
        Route::get('trips/recent', [TripController::class, 'recent'])->name('api.parent.trips.recent');
        Route::get('trips/today-status', [TripController::class, 'todayStatus'])->name('api.parent.trips.today-status');

        // Issues
        Route::get('issues', [IssueController::class, 'index'])->name('api.parent.issues.index');
        Route::post('issues', [IssueController::class, 'store'])->name('api.parent.issues.store');

        // Notifications + preferences
        Route::get('notifications', [NotificationController::class, 'index'])->name('api.parent.notifications.index');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('api.parent.notifications.mark-all-read');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('api.parent.notifications.read');

        Route::get('notification-preferences', [NotificationPreferenceController::class, 'show'])->name('api.parent.notification-preferences.show');
        Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('api.parent.notification-preferences.update');

        // Messaging to driver (per trip)
        Route::get('requests/{pickupRequest}/messages', [MessageController::class, 'index'])->name('api.parent.requests.messages.index');
        Route::post('requests/{pickupRequest}/messages', [MessageController::class, 'send'])->name('api.parent.requests.messages.send');
    });

    // Children / Students (Parent app only)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('students', [StudentController::class, 'index'])->name('api.parent.students.index');
        Route::post('students', [StudentController::class, 'store'])->name('api.parent.students.store');
        Route::get('students/{student}', [StudentController::class, 'show'])->name('api.parent.students.show');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('api.parent.students.update');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('api.parent.students.destroy');
    });
});

Route::prefix('self')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('api.self.register');
    Route::post('login', [AuthController::class, 'login'])->name('api.self.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.self.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.self.reset-password');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.self.logout');

        // Email verify (token-based for now)
        Route::post('email/verify/send', [AuthController::class, 'sendEmailVerification'])->name('api.self.email.verify.send');
        Route::post('email/verify', [AuthController::class, 'verifyEmail'])->name('api.self.email.verify');

        // Profile
        Route::get('me', [ProfileController::class, 'show'])->name('api.self.me.show');
        Route::put('me', [ProfileController::class, 'update'])->name('api.self.me.update');

        // Account settings
        Route::put('account/change-password', [AccountController::class, 'changePassword'])->name('api.self.account.change-password');
        Route::delete('account', [AccountController::class, 'deleteAccount'])->name('api.self.account.delete');

        // Locations
        Route::get('cities', [LocationController::class, 'cities'])->name('api.self.cities.index');
        Route::get('cities/{city}/areas', [LocationController::class, 'areas'])->name('api.self.cities.areas');

        // Pick-drop requests (trips)
        Route::get('requests', [RequestController::class, 'index'])->name('api.self.requests.index');
        Route::post('requests', [RequestController::class, 'store'])->name('api.self.requests.store');
        Route::get('requests/{requestId}', [RequestController::class, 'show'])->name('api.self.requests.show');
        Route::put('requests/{requestId}', [RequestController::class, 'update'])->name('api.self.requests.update');
        Route::delete('requests/{requestId}', [RequestController::class, 'cancel'])->name('api.self.requests.cancel');
        Route::get('requests/{requestId}/driver', [RequestController::class, 'driverInfo'])->name('api.self.requests.driver');
        Route::get('requests/{requestId}/tracking', [RequestController::class, 'tracking'])->name('api.self.requests.tracking');
        // Trips
        Route::get('trips/recent', [TripController::class, 'recent'])->name('api.self.trips.recent');
        Route::get('trips/today-status', [TripController::class, 'todayStatus'])->name('api.self.trips.today-status');

        // Issues
        Route::get('issues', [IssueController::class, 'index'])->name('api.self.issues.index');
        Route::post('issues', [IssueController::class, 'store'])->name('api.self.issues.store');

        // Notifications + preferences
        Route::get('notifications', [NotificationController::class, 'index'])->name('api.self.notifications.index');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('api.self.notifications.mark-all-read');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('api.self.notifications.read');

        Route::get('notification-preferences', [NotificationPreferenceController::class, 'show'])->name('api.self.notification-preferences.show');
        Route::put('notification-preferences', [NotificationPreferenceController::class, 'update'])->name('api.self.notification-preferences.update');

        // Messaging to driver (per trip)
        Route::get('requests/{pickupRequest}/messages', [MessageController::class, 'index'])->name('api.self.requests.messages.index');
        Route::post('requests/{pickupRequest}/messages', [MessageController::class, 'send'])->name('api.self.requests.messages.send');
    });
});

