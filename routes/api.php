<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Stateless JSON APIs for the driver and parent/self mobile apps.
| Laravel exposes these routes with the /api prefix.
|
*/

// Test route (optional)
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

// Driver and parent/self API groups live in separate files.
require base_path('routes/driver.php');
require base_path('routes/parent.php');

