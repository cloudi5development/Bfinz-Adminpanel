<?php

namespace App\Jobs;

use App\Exceptions\ApiException;
use App\Models\SyncRun;
use App\Services\Market\MetalRateSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs 4x/day (see routes/console.php). On failure, last good data in
 * metal_rates is left untouched — API reads compute a "stale" flag from
 * fetched_at rather than this job clearing anything on error.
 */
class SyncMetalRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function handle(MetalRateSyncService $service): void
    {
        $startedAt = now();

        try {
            $records = $service->sync();

            SyncRun::create([
                'job' => 'SyncMetalRates',
                'status' => 'success',
                'records' => $records,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            EvaluateAlerts::dispatch('gold');
            EvaluateAlerts::dispatch('silver');
        } catch (ApiException $e) {
            Log::warning('SyncMetalRates failed, keeping last good data', ['error' => $e->getMessage()]);

            SyncRun::create([
                'job' => 'SyncMetalRates',
                'status' => 'failed',
                'records' => 0,
                'error' => $e->getMessage(),
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);
        }
    }
}
