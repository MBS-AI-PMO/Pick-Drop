<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Yahan par mobile apps (driver / parent-self) ke liye saare stateless
| JSON APIs register hon ge. Ye /api prefix ke sath automatically expose
| hote hain (Laravel default).
|
*/

// Test route (optional)
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

// Driver & Parent/Self API groups ko separate files se include karein
require base_path('routes/driver.php');
require base_path('routes/parent.php');

