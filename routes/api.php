<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AppConfigController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ForexController;
use App\Http\Controllers\Api\V1\FuelController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\MasterController;
use App\Http\Controllers\Api\V1\MetalController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SearchController;
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

    // Reference data + remote config: public, long-cached (see MasterController
    // and AppConfigController docblocks for TTLs).
    Route::get('/app/config', [AppConfigController::class, 'show'])->name('app.config');
    Route::controller(MasterController::class)->prefix('master')->name('master.')->group(function () {
        Route::get('/states', 'states')->name('states');
        Route::get('/cities', 'cities')->name('cities');
        Route::get('/banks', 'banks')->name('banks');
    });

    // Market data: read-only, always served from DB (SyncMetalRates /
    // SyncForexRates write it on schedule — see routes/console.php). Never
    // calls a provider during a user request.
    Route::controller(MetalController::class)->prefix('metals/{metal}')->name('metals.')->group(function () {
        Route::get('/live', 'live')->name('live');
        Route::get('/details', 'details')->name('details');
        Route::get('/trend', 'trend')->name('trend');
        Route::get('/cities', 'cities')->name('cities');
    });

    Route::controller(ForexController::class)->prefix('forex')->name('forex.')->group(function () {
        Route::get('/rates', 'rates')->name('rates');
        Route::get('/movers', 'movers')->name('movers');
        Route::get('/currencies', 'currencies')->name('currencies');
        Route::get('/{code}', 'show')->name('show')->where('code', '[A-Za-z]{3}');
    });

    Route::controller(FuelController::class)->prefix('fuel')->name('fuel.')->group(function () {
        Route::get('/prices', 'prices')->name('prices');
        Route::get('/trend', 'trend')->name('trend');
        Route::get('/cities', 'cities')->name('cities');
    });

    // Public, but personalised when a bearer token is present — see
    // HomeController/SearchController docblocks for why these aren't
    // behind auth:sanctum middleware.
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::controller(SearchController::class)->prefix('search')->name('search.')->group(function () {
        Route::get('/', 'search')->name('index');
        Route::get('/suggestions', 'suggestions')->name('suggestions');
    });

    // Public mobile OTP login. throttle:X,1 rate-limits per IP; OtpService
    // adds a second, mobile-number-keyed rolling-window/attempt-count layer.
    Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
        Route::post('/send-otp', 'sendOtp')->middleware('throttle:3,1')->name('send-otp');
        Route::post('/resend-otp', 'resendOtp')->middleware('throttle:3,1')->name('resend-otp');
        Route::post('/verify-otp', 'verifyOtp')->middleware('throttle:10,1')->name('verify-otp');
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(AuthController::class)->prefix('auth')->name('auth.')->group(function () {
            Route::get('/profile', 'profile')->name('profile');
            Route::post('/update-profile', 'updateProfile')->name('update-profile');
            Route::delete('/profile', 'deleteProfile')->name('profile.delete');
            Route::post('/devices', 'registerDevice')->name('devices');
            Route::get('/login-history', 'loginHistory')->name('login-history');
            Route::post('/logout', 'logout')->name('logout');
            Route::post('/logout-all', 'logoutAll')->name('logout-all');
        });

        Route::controller(AlertController::class)->prefix('alerts')->name('alerts.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{alert}', 'update')->name('update');
            Route::delete('/{alert}', 'destroy')->name('destroy');
        });

        Route::controller(NotificationController::class)->prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::patch('/{notification}/read', 'markRead')->name('read');
            Route::post('/read-all', 'markAllRead')->name('read-all');
            Route::delete('/{notification}', 'destroy')->name('destroy');
            Route::delete('/', 'destroyAll')->name('destroy-all');
        });

        Route::controller(SearchController::class)->prefix('search')->name('search.')->group(function () {
            Route::get('/recent', 'recent')->name('recent');
            Route::delete('/recent', 'clearRecent')->name('recent.clear');
        });
    });
});
