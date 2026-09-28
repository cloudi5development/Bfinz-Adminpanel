<?php

use App\Jobs\SyncForexRates;
use App\Jobs\SyncMetalRates;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Market data sync — see docs/BUILD_SPEC.md §6. Times are server-local; the
// app timezone should be set to Asia/Kolkata for these to land at the
// IST times the spec calls for.
Schedule::job(new SyncMetalRates)->dailyAt('09:00');
Schedule::job(new SyncMetalRates)->dailyAt('12:00');
Schedule::job(new SyncMetalRates)->dailyAt('15:00');
Schedule::job(new SyncMetalRates)->dailyAt('18:00');

Schedule::job(new SyncForexRates)->dailyAt('09:15');
Schedule::job(new SyncForexRates)->dailyAt('18:15');
