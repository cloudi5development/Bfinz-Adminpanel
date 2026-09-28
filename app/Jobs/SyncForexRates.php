<?php

namespace App\Jobs;

use App\Exceptions\ApiException;
use App\Models\SyncRun;
use App\Services\Market\ForexRateSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs 2x/day (see routes/console.php). On failure, last good data in
 * forex_rates is left untouched.
 */
class SyncForexRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function handle(ForexRateSyncService $service): void
    {
        $startedAt = now();

        try {
            $records = $service->sync();

            SyncRun::create([
                'job' => 'SyncForexRates',
                'status' => 'success',
                'records' => $records,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            EvaluateAlerts::dispatch('forex');
        } catch (ApiException $e) {
            Log::warning('SyncForexRates failed, keeping last good data', ['error' => $e->getMessage()]);

            SyncRun::create([
                'job' => 'SyncForexRates',
                'status' => 'failed',
                'records' => 0,
                'error' => $e->getMessage(),
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);
        }
    }
}
