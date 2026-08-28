<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PickDropChargeController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\SchoolRouteController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DriverVerificationController;
use App\Http\Controllers\VehicleVerificationController;
use App\Http\Controllers\ParentSelfVerificationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentSettingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PickupRequestController;
use App\Http\Controllers\IssueController as AdminIssueController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\SosAlertController;
use App\Http\Controllers\PlatformSettingController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\LocalPaymentCallbackController;


Route::get('/', function () {
    return view('pages.auth.login');
})->name('login'); // default landing redirects to login

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // PickDrop Domain Routes
    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
    Route::get('/driver-verifications', [DriverVerificationController::class, 'index'])->name('driver-verifications.index');
    Route::get('/driver-verifications/{driverVerification}', [DriverVerificationController::class, 'show'])->name('driver-verifications.show');
    Route::post('/driver-verifications/{driverVerification}/status', [DriverVerificationController::class, 'updateStatus'])->name('driver-verifications.status');
    Route::post('/driver-verifications/{driverVerification}/approve', [DriverVerificationController::class, 'approve'])->name('driver-verifications.approve');
    Route::post('/driver-verifications/{driverVerification}/reject', [DriverVerificationController::class, 'reject'])->name('driver-verifications.reject');
    Route::get('/driver-verifications/{driverVerification}/document/{field}', [DriverVerificationController::class, 'document'])->name('driver-verifications.document');
    Route::get('/pickup-requests', [PickupRequestController::class, 'index'])->name('pickup-requests.index');
    Route::get('/pickup-requests/{pickupRequest}', [PickupRequestController::class, 'show'])->name('pickup-requests.show');
    Route::post('/pickup-requests/{pickupRequest}/driver-payout', [PickupRequestController::class, 'markDriverPaid'])->name('pickup-requests.driver-payout');
    Route::post('/pickup-requests/{pickupRequest}/assign', [PickupRequestController::class, 'assignDriver'])->name('pickup-requests.assign');
    Route::get('/issues', [AdminIssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/{issueReport}', [AdminIssueController::class, 'show'])->name('issues.show');
    Route::post('/issues/{issueReport}/status', [AdminIssueController::class, 'updateStatus'])->name('issues.status');
    Route::get('/sos', [SosAlertController::class, 'index'])->name('sos.index');
    Route::get('/sos/{sosAlert}', [SosAlertController::class, 'show'])->name('sos.show');
    Route::post('/sos/{sosAlert}/acknowledge', [SosAlertController::class, 'acknowledge'])->name('sos.acknowledge');
    Route::post('/sos/{sosAlert}/resolve', [SosAlertController::class, 'resolve'])->name('sos.resolve');
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
    Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
    Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
    Route::put('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
    Route::delete('/schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    Route::get('/platform-settings', [PlatformSettingController::class, 'edit'])->name('platform-settings.edit');
    Route::put('/platform-settings', [PlatformSettingController::class, 'update'])->name('platform-settings.update');
    Route::get('/parent-self-verifications', [ParentSelfVerificationController::class, 'index'])->name('parent-self-verifications.index');
    Route::get('/parent-self-verifications/{parentSelfVerification}', [ParentSelfVerificationController::class, 'show'])->name('parent-self-verifications.show');
    Route::post('/parent-self-verifications/{parentSelfVerification}/status', [ParentSelfVerificationController::class, 'updateStatus'])->name('parent-self-verifications.status');
    Route::post('/parent-self-verifications/{parentSelfVerification}/approve', [ParentSelfVerificationController::class, 'approve'])->name('parent-self-verifications.approve');
    Route::post('/parent-self-verifications/{parentSelfVerification}/reject', [ParentSelfVerificationController::class, 'reject'])->name('parent-self-verifications.reject');
    Route::get('/parent-self-verifications/{parentSelfVerification}/document/{field}', [ParentSelfVerificationController::class, 'document'])->name('parent-self-verifications.document');
    Route::get('/vehicle-verifications', [VehicleVerificationController::class, 'index'])->name('vehicle-verifications.index');
    Route::get('/vehicle-verifications/{vehicleVerification}', [VehicleVerificationController::class, 'show'])->name('vehicle-verifications.show');
    Route::post('/vehicle-verifications/{vehicleVerification}/status', [VehicleVerificationController::class, 'updateStatus'])->name('vehicle-verifications.status');
    Route::post('/vehicle-verifications/{vehicleVerification}/approve', [VehicleVerificationController::class, 'approve'])->name('vehicle-verifications.approve');
    Route::post('/vehicle-verifications/{vehicleVerification}/reject', [VehicleVerificationController::class, 'reject'])->name('vehicle-verifications.reject');
    Route::get('/vehicle-verifications/{vehicleVerification}/document/{field}', [VehicleVerificationController::class, 'document'])->name('vehicle-verifications.document');
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
    Route::get('/payments', [InvoiceController::class, 'index'])->name('payments.index');
    Route::post('/payments/invoices', [InvoiceController::class, 'store'])->name('payments.store');
    Route::get('/payments/export', [InvoiceController::class, 'export'])->name('payments.export');
    Route::get('/payments/settings', [PaymentSettingController::class, 'edit'])->name('payments.settings');
    Route::put('/payments/settings', [PaymentSettingController::class, 'update'])->name('payments.settings.update');
    Route::get('/payments/invoices/{invoice}', [InvoiceController::class, 'show'])->name('payments.show');
    Route::get('/payments/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('payments.print');
    Route::post('/payments/invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('payments.send');
    Route::post('/payments/invoices/{invoice}/pay', [InvoiceController::class, 'recordPayment'])->name('payments.record');
    Route::post('/payments/invoices/{invoice}/stripe', [InvoiceController::class, 'stripeCheckout'])->name('payments.stripe');
    Route::post('/payments/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('payments.cancel');
    Route::delete('/payments/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('payments.destroy');
    Route::delete('/payments/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('payments.payments.destroy');
    Route::post('/payments/payments/{payment}/confirm-bank', [InvoiceController::class, 'confirmBank'])->name('payments.confirm-bank');
    Route::post('/payments/payments/{payment}/reject-bank', [InvoiceController::class, 'rejectBank'])->name('payments.reject-bank');
    Route::post('/payments/payments/{payment}/refund', [InvoiceController::class, 'refundPayment'])->name('payments.refund');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
    Route::get('/charges', [PickDropChargeController::class, 'index'])->name('charges.index');
    Route::put('/charges', [PickDropChargeController::class, 'update'])->name('charges.update');
    Route::get('/profile', [ProfileController::class, 'index'])->name('general.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::middleware('super.admin')->group(function () {
        Route::post('/profile/admins', [ProfileController::class, 'storeAdmin'])->name('profile.admins.store');
        Route::put('/profile/admins/{user}', [ProfileController::class, 'updateAdmin'])->name('profile.admins.update');
        Route::delete('/profile/admins/{user}', [ProfileController::class, 'destroyAdmin'])->name('profile.admins.destroy');
    });
    Route::get('/notifications', [NotificationController::class,'index'])
    ->name('notifications.index');
    Route::get('/notifications/clear', [NotificationController::class, 'clear'])
    ->name('notifications.clear');
    Route::post('/vehicles/{vehicle}/unassign', [VehicleController::class, 'unassign'])
    ->name('vehicles.unassign');
});

// Auth Routes (Public)
Route::group(['prefix' => 'auth'], function(){
    Route::get('login', function () { return view('pages.auth.login'); })->name('auth.login');
    Route::get('register', function () { return view('pages.auth.register'); })->name('auth.register');
    Route::get('forgot-password', function () { return view('pages.auth.forgot-password'); })->name('auth.forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');

    // Auth form submissions
    Route::post('login', [AuthController::class, 'login'])
        ->name('login'); // Keep name as 'login' for compatibility with auth middleware & redirects

    Route::post('register', [AuthController::class, 'register'])
        ->name('auth.register.submit');

    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
        ->name('auth.forgot-password.submit');
});
Route::get('reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
    ->name('password.reset');

Route::post('/stripe/webhook', StripeWebhookController::class)->name('stripe.webhook');
Route::post('/payments/jazzcash/callback', [LocalPaymentCallbackController::class, 'jazzcash'])->name('payments.jazzcash.callback');
Route::post('/payments/easypaisa/callback', [LocalPaymentCallbackController::class, 'easypaisa'])->name('payments.easypaisa.callback');
Route::get('/payments/stripe/complete', [InvoiceController::class, 'stripeComplete'])->name('payments.stripe.complete');
Route::get('/payments/stripe/cancel/{invoice}', [InvoiceController::class, 'stripeCancel'])->name('payments.stripe.cancel');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
})->name('clear-cache');

// HTML 404 for unknown admin pages only. Do not use Route::any() here —
// it intercepts API POSTs and returns "CSRF token mismatch" instead of JSON.
Route::get('/{page?}', function () {
    return View::make('pages.error.404');
})->where('page', '^(?!api/).*$');
Route::get('/notifications', [NotificationController::class,'index'])
    ->name('notifications.index');
