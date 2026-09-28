<?php

namespace App\Providers;

use App\Contracts\Providers\ForexRateProvider;
use App\Contracts\Providers\MetalRateProvider;
use App\Contracts\Providers\PushSender;
use App\Providers\Data\AdminManualMetalRateProvider;
use App\Providers\Data\FrankfurterForexRateProvider;
use App\Providers\Push\LogPushSender;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MetalRateProvider::class, match (config('services.metal_rate_provider')) {
            default => AdminManualMetalRateProvider::class,
        });

        $this->app->bind(ForexRateProvider::class, match (config('services.forex_rate_provider')) {
            default => FrankfurterForexRateProvider::class,
        });

        $this->app->bind(PushSender::class, match (config('services.push_sender')) {
            default => LogPushSender::class,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        $this->configureRateLimiting();
    }

    /**
     * Define the "api" rate limiter used by routes/api.php (throttle:api):
     * 60 requests per minute, keyed by authenticated user or client IP.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
