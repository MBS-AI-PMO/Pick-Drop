<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Driver\AuthController;
use App\Http\Controllers\Api\Driver\ProfileController;
use App\Http\Controllers\Api\Driver\AccountController;
use App\Http\Controllers\Api\Driver\ServiceAreaController;
use App\Http\Controllers\Api\Driver\RequestController;
use App\Http\Controllers\Api\Driver\RideController;
use App\Http\Controllers\Api\Driver\IssueController;
use App\Http\Controllers\Api\Driver\NotificationController;
use App\Http\Controllers\Api\Driver\MessageController;
use App\Http\Controllers\Api\Driver\VerificationController;
use App\Http\Controllers\Api\Driver\VehicleVerificationController;
use App\Http\Controllers\Api\Driver\RatingController;
use App\Http\Controllers\Api\Driver\SosController;
use App\Http\Controllers\Api\ParentSelf\LocationController;
use App\Http\Controllers\Api\ParentSelf\AttendanceController;
use App\Http\Controllers\Api\Driver\EarningsController;

/*
|--------------------------------------------------------------------------
| Driver Mobile App API Routes
|--------------------------------------------------------------------------
|
| Prefix automatically ho jayega /api/driver (Laravel api.php prefix + group).
|
*/

Route::prefix('driver')->group(function () {

    // Step 1 Auth: name, phone, email, password, referral_code (optional)
    Route::post('register', [AuthController::class, 'register'])->name('api.driver.register');
    Route::post('login',    [AuthController::class, 'login'])->name('api.driver.login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('api.driver.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('api.driver.reset-password');
    Route::post('logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum')->name('api.driver.logout');
    Route::get('cities', [LocationController::class, 'cities'])->name('api.driver.cities.index');
    Route::get('cities/{city}/areas', [LocationController::class, 'areas'])->name('api.driver.cities.areas');
    Route::post('/verify-email', [AuthController::class, 'verifyOtp']);
    Route::post('/resend/otp', [AuthController::class, 'resendOtp']);

    Route::middleware('auth:sanctum')->group(function () {
        // Step 2: Driver Verification (KYC) — no vehicle info
        Route::get('verification', [VerificationController::class, 'show'])->name('api.driver.verification.show');
        Route::post('verification', [VerificationController::class, 'store'])->name('api.driver.verification.store');

        // Step 3: Vehicle verification — car details and documents
        Route::get('vehicle-verification', [VehicleVerificationController::class, 'show'])->name('api.driver.vehicle-verification.show');
        Route::post('vehicle-verification', [VehicleVerificationController::class, 'store'])->name('api.driver.vehicle-verification.store');

        // Profile (city_id, service_areas[], home_address, name, phone)
        Route::get('me', [ProfileController::class, 'show'])->name('api.driver.me.show');
        Route::put('me', [ProfileController::class, 'update'])->name('api.driver.me.update');
        Route::put('account/change-password', [AccountController::class, 'changePassword'])->name('api.driver.account.change-password');
        Route::delete('account', [AccountController::class, 'deleteAccount'])->name('api.driver.account.delete');

        // Optional later update of service areas (selected during KYC personal info)
        Route::get('service-areas',  [ServiceAreaController::class, 'index'])->name('api.driver.service-areas.index');
        Route::post('service-areas', [ServiceAreaController::class, 'sync'])->name('api.driver.service-areas.sync');

        // Parent/Self requests
        Route::get('requests/available', [RequestController::class, 'available'])->name('api.driver.requests.available');
        Route::get('requests/accepted', [RequestController::class, 'accepted'])->name('api.driver.requests.accepted');
        Route::get('requests/cover', [\App\Http\Controllers\Api\Driver\CoverController::class, 'available'])->name('api.driver.requests.cover');
        Route::post('requests/{pickupRequest}/accept', [RequestController::class, 'accept'])->name('api.driver.requests.accept');
        Route::post('requests/{pickupRequest}/reject', [RequestController::class, 'reject'])->name('api.driver.requests.reject');
        Route::post('requests/{pickupRequest}/status', [RequestController::class, 'updateStatus'])->name('api.driver.requests.status');

        // Messaging with parent/self (per accepted pickup request)
        Route::get('requests/{pickupRequest}/messages', [MessageController::class, 'index'])->name('api.driver.requests.messages.index');
        Route::post('requests/{pickupRequest}/messages', [MessageController::class, 'send'])->name('api.driver.requests.messages.send');

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

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('api.driver.notifications.index');
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('api.driver.notifications.mark-all-read');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('api.driver.notifications.read');

        Route::post('phone/verify/send', [AuthController::class, 'sendPhoneVerification'])->name('api.driver.phone.verify.send');
        Route::post('phone/verify', [AuthController::class, 'verifyPhone'])->name('api.driver.phone.verify');

        Route::post('sos', [SosController::class, 'store'])->name('api.driver.sos.store');
        Route::post('requests/{pickupRequest}/ratings', [RatingController::class, 'store'])->name('api.driver.requests.ratings.store');
        Route::get('holidays', [AttendanceController::class, 'holidays'])->name('api.driver.holidays');
        Route::get('earnings', [EarningsController::class, 'index'])->name('api.driver.earnings');
        Route::get('payrolls', [\App\Http\Controllers\Api\Driver\CoverController::class, 'payrolls'])->name('api.driver.payrolls.index');
        Route::get('payrolls/{payroll}', [\App\Http\Controllers\Api\Driver\CoverController::class, 'payroll'])->name('api.driver.payrolls.show');
        Route::post('requests/{pickupRequest}/unavailable', [\App\Http\Controllers\Api\Driver\CoverController::class, 'unavailable'])->name('api.driver.requests.unavailable');
        Route::post('cover/{replacement}/accept', [\App\Http\Controllers\Api\Driver\CoverController::class, 'accept'])->name('api.driver.cover.accept');
        Route::post('device-token', [EarningsController::class, 'registerDevice'])->name('api.driver.device-token');
        Route::get('requests/{pickupRequest}/contact', [EarningsController::class, 'contact'])->name('api.driver.requests.contact');
    });
});

