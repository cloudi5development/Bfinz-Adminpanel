<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Loaded with the "/api" prefix and the "api" middleware group. Every module
| is versioned under /api/v1 and namespaced App\Http\Controllers\Api\V1 so a
| future /api/v2 can live alongside it without breaking existing clients.
|
| Responses use the standard App\Support\ApiResponse envelope (see the
| ApiResponser trait on App\Http\Controllers\Api\Controller). The "api" rate
| limiter is defined in App\Providers\AppServiceProvider.
|
*/

Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    Route::get('/health', HealthController::class)->name('health');

    // Public mobile OTP login. throttle:X,1 rate-limits per IP; OtpService
    // adds a second, mobile-number-keyed rolling-window/attempt-count layer.
    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('/send-otp', 'sendOtp')->middleware('throttle:3,1')->name('send-otp');
        Route::post('/verify-otp', 'verifyOtp')->middleware('throttle:10,1')->name('verify-otp');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/update-profile', 'updateProfile')->name('update-profile');
            Route::get('/login-history', 'loginHistory')->name('login-history');
            Route::post('/logout', 'logout')->name('logout');
            Route::post('/logout-all', 'logoutAll')->name('logout-all');
        });
    });
});
