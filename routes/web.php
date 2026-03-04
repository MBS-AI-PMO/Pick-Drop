<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.auth.login');
})->name('login');

// Protected Admin Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // PickDrop Domain Routes
    Route::get('/users', function () { return view('pickdrop.users.index'); })->name('users.index');
    Route::get('/vehicles', function () { return view('pickdrop.vehicles.index'); })->name('vehicles.index');
    Route::get('/routes', function () { return view('pickdrop.routes.index'); })->name('routes.index');
    Route::get('/routes/create', function () { return view('pickdrop.routes.create'); })->name('routes.create');
    Route::get('/routes/{id}/edit', function ($id) { return view('pickdrop.routes.edit'); })->name('routes.edit');
    Route::get('/payments', function () { return view('pickdrop.payments.index'); })->name('payments.index');
    Route::get('/reports', function () { return view('pickdrop.reports.index'); })->name('reports.index');

    Route::group(['prefix' => 'email'], function(){
        Route::get('inbox', function () { return view('pages.email.inbox'); })->name('email.inbox');
        Route::get('read', function () { return view('pages.email.read'); })->name('email.read');
        Route::get('compose', function () { return view('pages.email.compose'); })->name('email.compose');
    });

    Route::group(['prefix' => 'apps'], function(){
        Route::get('chat', function () { return view('pages.apps.chat'); })->name('apps.chat');
        Route::get('calendar', function () { return view('pages.apps.calendar'); })->name('apps.calendar');
    });

    Route::group(['prefix' => 'ui-components'], function(){
        Route::get('accordion', function () { return view('pages.ui-components.accordion'); })->name('ui-components.accordion');
        Route::get('alerts', function () { return view('pages.ui-components.alerts'); })->name('ui-components.alerts');
        Route::get('badges', function () { return view('pages.ui-components.badges'); })->name('ui-components.badges');
        Route::get('breadcrumbs', function () { return view('pages.ui-components.breadcrumbs'); })->name('ui-components.breadcrumbs');
        Route::get('buttons', function () { return view('pages.ui-components.buttons'); })->name('ui-components.buttons');
        Route::get('button-group', function () { return view('pages.ui-components.button-group'); })->name('ui-components.button-group');
        Route::get('cards', function () { return view('pages.ui-components.cards'); })->name('ui-components.cards');
        Route::get('carousel', function () { return view('pages.ui-components.carousel'); })->name('ui-components.carousel');
        Route::get('collapse', function () { return view('pages.ui-components.collapse'); })->name('ui-components.collapse');
        Route::get('dropdowns', function () { return view('pages.ui-components.dropdowns'); })->name('ui-components.dropdowns');
        Route::get('list-group', function () { return view('pages.ui-components.list-group'); })->name('ui-components.list-group');
        Route::get('media-object', function () { return view('pages.ui-components.media-object'); })->name('ui-components.media-object');
        Route::get('modal', function () { return view('pages.ui-components.modal'); })->name('ui-components.modal');
        Route::get('navs', function () { return view('pages.ui-components.navs'); })->name('ui-components.navs');
        Route::get('offcanvas', function () { return view('pages.ui-components.offcanvas'); })->name('ui-components.offcanvas');
        Route::get('pagination', function () { return view('pages.ui-components.pagination'); })->name('ui-components.pagination');
        Route::get('placeholders', function () { return view('pages.ui-components.placeholders'); })->name('ui-components.placeholders');
        Route::get('popovers', function () { return view('pages.ui-components.popovers'); })->name('ui-components.popovers');
        Route::get('progress', function () { return view('pages.ui-components.progress'); })->name('ui-components.progress');
        Route::get('scrollbar', function () { return view('pages.ui-components.scrollbar'); })->name('ui-components.scrollbar');
        Route::get('scrollspy', function () { return view('pages.ui-components.scrollspy'); })->name('ui-components.scrollspy');
        Route::get('spinners', function () { return view('pages.ui-components.spinners'); })->name('ui-components.spinners');
        Route::get('tabs', function () { return view('pages.ui-components.tabs'); })->name('ui-components.tabs');
        Route::get('toasts', function () { return view('pages.ui-components.toasts'); })->name('ui-components.toasts');
        Route::get('tooltips', function () { return view('pages.ui-components.tooltips'); })->name('ui-components.tooltips');
    });

    Route::group(['prefix' => 'advanced-ui'], function(){
        Route::get('cropper', function () { return view('pages.advanced-ui.cropper'); })->name('advanced-ui.cropper');
        Route::get('owl-carousel', function () { return view('pages.advanced-ui.owl-carousel'); })->name('advanced-ui.owl-carousel');
        Route::get('sortablejs', function () { return view('pages.advanced-ui.sortablejs'); })->name('advanced-ui.sortablejs');
        Route::get('sweet-alert', function () { return view('pages.advanced-ui.sweet-alert'); })->name('advanced-ui.sweet-alert');
    });

    Route::group(['prefix' => 'forms'], function(){
        Route::get('basic-elements', function () { return view('pages.forms.basic-elements'); })->name('forms.basic-elements');
        Route::get('advanced-elements', function () { return view('pages.forms.advanced-elements'); })->name('forms.advanced-elements');
        Route::get('editors', function () { return view('pages.forms.editors'); })->name('forms.editors');
        Route::get('wizard', function () { return view('pages.forms.wizard'); })->name('forms.wizard');
    });

    Route::group(['prefix' => 'charts'], function(){
        Route::get('apex', function () { return view('pages.charts.apex'); })->name('charts.apex');
        Route::get('chartjs', function () { return view('pages.charts.chartjs'); })->name('charts.chartjs');
        Route::get('flot', function () { return view('pages.charts.flot'); })->name('charts.flot');
        Route::get('peity', function () { return view('pages.charts.peity'); })->name('charts.peity');
        Route::get('sparkline', function () { return view('pages.charts.sparkline'); })->name('charts.sparkline');
    });

    Route::group(['prefix' => 'tables'], function(){
        Route::get('basic-tables', function () { return view('pages.tables.basic-tables'); })->name('tables.basic-tables');
        Route::get('data-table', function () { return view('pages.tables.data-table'); })->name('tables.data-table');
    });

    Route::group(['prefix' => 'icons'], function(){
        Route::get('lucide-icons', function () { return view('pages.icons.lucide-icons'); })->name('icons.lucide-icons');
        Route::get('flag-icons', function () { return view('pages.icons.flag-icons'); })->name('icons.flag-icons');
        Route::get('mdi-icons', function () { return view('pages.icons.mdi-icons'); })->name('icons.mdi-icons');
    });

    Route::group(['prefix' => 'general'], function(){
        Route::get('blank-page', function () { return view('pages.general.blank-page'); })->name('general.blank-page');
        Route::get('faq', function () { return view('pages.general.faq'); })->name('general.faq');
        Route::get('invoice', function () { return view('pages.general.invoice'); })->name('general.invoice');
        Route::get('profile', function () { return view('pages.general.profile'); })->name('general.profile');
        Route::get('pricing', function () { return view('pages.general.pricing'); })->name('general.pricing');
        Route::get('timeline', function () { return view('pages.general.timeline'); })->name('general.timeline');
    });
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

Route::group(['prefix' => 'error'], function(){
    Route::get('404', function () { return view('pages.error.404'); })->name('error.404');
    Route::get('500', function () { return view('pages.error.500'); })->name('error.500');
});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
})->name('clear-cache');

// 404 for undefined routes
Route::any('/{page?}',function(){
    return View::make('pages.error.404');
})->where('page','.*');

