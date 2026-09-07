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
use App\Http\Controllers\Api\ParentSelf\VerificationController;
use App\Http\Controllers\Api\ParentSelf\CommuteProfileController;
use App\Http\Controllers\Api\ParentSelf\InvoiceController;
use App\Http\Controllers\Api\ParentSelf\AttendanceController;
use App\Http\Controllers\Api\ParentSelf\RatingController;
use App\Http\Controllers\Api\ParentSelf\SosController;
use App\Http\Controllers\Api\ParentSelf\PlatformController;

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
        Route::post('phone/verify/send', [AuthController::class, 'sendPhoneVerification'])->name('api.parent.phone.verify.send');
        Route::post('phone/verify', [AuthController::class, 'verifyPhone'])->name('api.parent.phone.verify');

        Route::get('verification', [VerificationController::class, 'show'])->name('api.parent.verification.show');
        Route::post('verification', [VerificationController::class, 'store'])->name('api.parent.verification.store');

        Route::get('payment-methods', [InvoiceController::class, 'methods'])->name('api.parent.payment-methods');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('api.parent.invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.parent.invoices.show');
        Route::post('invoices/{invoice}/pay/stripe', [InvoiceController::class, 'payStripe'])->name('api.parent.invoices.pay.stripe');
        Route::post('invoices/{invoice}/pay/bank', [InvoiceController::class, 'payBank'])->name('api.parent.invoices.pay.bank');
        Route::post('invoices/{invoice}/pay/jazzcash', [InvoiceController::class, 'payJazzcash'])->name('api.parent.invoices.pay.jazzcash');
        Route::post('invoices/{invoice}/pay/easypaisa', [InvoiceController::class, 'payEasypaisa'])->name('api.parent.invoices.pay.easypaisa');

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
        Route::get('requests/{requestId}/replacements', [RequestController::class, 'replacements'])->name('api.parent.requests.replacements');
        Route::post('requests/{requestId}/renew', [RequestController::class, 'renew'])->name('api.parent.requests.renew');
        Route::post('requests/{requestId}/auto-renew', [RequestController::class, 'autoRenew'])->name('api.parent.requests.auto-renew');
        Route::post('requests/{requestId}/decline-renewal', [RequestController::class, 'declineRenewal'])->name('api.parent.requests.decline-renewal');
        Route::get('requests/{requestId}/attendance', [AttendanceController::class, 'index'])->name('api.parent.requests.attendance');
        Route::post('requests/{requestId}/attendance/skip', [AttendanceController::class, 'skip'])->name('api.parent.requests.attendance.skip');
        Route::delete('requests/{requestId}/attendance/skip', [AttendanceController::class, 'unskip'])->name('api.parent.requests.attendance.unskip');
        Route::get('requests/{requestId}/ratings', [RatingController::class, 'index'])->name('api.parent.requests.ratings.index');
        Route::post('requests/{requestId}/ratings', [RatingController::class, 'store'])->name('api.parent.requests.ratings.store');
        Route::get('holidays', [AttendanceController::class, 'holidays'])->name('api.parent.holidays');
        Route::get('sos', [SosController::class, 'index'])->name('api.parent.sos.index');
        Route::post('sos', [SosController::class, 'store'])->name('api.parent.sos.store');
        Route::post('device-token', [PlatformController::class, 'registerDevice'])->name('api.parent.device-token');
        Route::get('schools', [PlatformController::class, 'schools'])->name('api.parent.schools');
        Route::get('wallet', [PlatformController::class, 'wallet'])->name('api.parent.wallet');
        Route::get('requests/{requestId}/contact', [PlatformController::class, 'contact'])->name('api.parent.requests.contact');
        Route::get('requests/{requestId}/cancellation-preview', [PlatformController::class, 'cancellationPreview'])->name('api.parent.requests.cancellation-preview');
        Route::get('requests/{requestId}/otp', [PlatformController::class, 'todayOtp'])->name('api.parent.requests.otp');

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
        Route::post('phone/verify/send', [AuthController::class, 'sendPhoneVerification'])->name('api.self.phone.verify.send');
        Route::post('phone/verify', [AuthController::class, 'verifyPhone'])->name('api.self.phone.verify');

        Route::get('verification', [VerificationController::class, 'show'])->name('api.self.verification.show');
        Route::post('verification', [VerificationController::class, 'store'])->name('api.self.verification.store');

        Route::get('commute-profile', [CommuteProfileController::class, 'show'])->name('api.self.commute-profile.show');
        Route::post('commute-profile', [CommuteProfileController::class, 'store'])->name('api.self.commute-profile.store');

        Route::get('payment-methods', [InvoiceController::class, 'methods'])->name('api.self.payment-methods');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('api.self.invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('api.self.invoices.show');
        Route::post('invoices/{invoice}/pay/stripe', [InvoiceController::class, 'payStripe'])->name('api.self.invoices.pay.stripe');
        Route::post('invoices/{invoice}/pay/bank', [InvoiceController::class, 'payBank'])->name('api.self.invoices.pay.bank');
        Route::post('invoices/{invoice}/pay/jazzcash', [InvoiceController::class, 'payJazzcash'])->name('api.self.invoices.pay.jazzcash');
        Route::post('invoices/{invoice}/pay/easypaisa', [InvoiceController::class, 'payEasypaisa'])->name('api.self.invoices.pay.easypaisa');

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
        Route::get('requests/{requestId}/replacements', [RequestController::class, 'replacements'])->name('api.self.requests.replacements');
        Route::post('requests/{requestId}/renew', [RequestController::class, 'renew'])->name('api.self.requests.renew');
        Route::post('requests/{requestId}/auto-renew', [RequestController::class, 'autoRenew'])->name('api.self.requests.auto-renew');
        Route::post('requests/{requestId}/decline-renewal', [RequestController::class, 'declineRenewal'])->name('api.self.requests.decline-renewal');
        Route::get('requests/{requestId}/attendance', [AttendanceController::class, 'index'])->name('api.self.requests.attendance');
        Route::post('requests/{requestId}/attendance/skip', [AttendanceController::class, 'skip'])->name('api.self.requests.attendance.skip');
        Route::delete('requests/{requestId}/attendance/skip', [AttendanceController::class, 'unskip'])->name('api.self.requests.attendance.unskip');
        Route::get('requests/{requestId}/ratings', [RatingController::class, 'index'])->name('api.self.requests.ratings.index');
        Route::post('requests/{requestId}/ratings', [RatingController::class, 'store'])->name('api.self.requests.ratings.store');
        Route::get('holidays', [AttendanceController::class, 'holidays'])->name('api.self.holidays');
        Route::get('sos', [SosController::class, 'index'])->name('api.self.sos.index');
        Route::post('sos', [SosController::class, 'store'])->name('api.self.sos.store');
        Route::post('device-token', [PlatformController::class, 'registerDevice'])->name('api.self.device-token');
        Route::get('schools', [PlatformController::class, 'schools'])->name('api.self.schools');
        Route::get('wallet', [PlatformController::class, 'wallet'])->name('api.self.wallet');
        Route::get('requests/{requestId}/contact', [PlatformController::class, 'contact'])->name('api.self.requests.contact');
        Route::get('requests/{requestId}/cancellation-preview', [PlatformController::class, 'cancellationPreview'])->name('api.self.requests.cancellation-preview');
        Route::get('requests/{requestId}/otp', [PlatformController::class, 'todayOtp'])->name('api.self.requests.otp');
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

