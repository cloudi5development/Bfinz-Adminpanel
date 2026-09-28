<?php

namespace App\Services\Market;

use App\Contracts\Providers\ForexRateProvider;
use App\Models\Currency;
use App\Models\ForexRate;

/**
 * Writes today's forex_rates rows (quote always INR) for every active
 * currency, from the bound ForexRateProvider. Same daily-upsert idempotency
 * approach as MetalRateSyncService.
 */
class ForexRateSyncService
{
    public function __construct(private readonly ForexRateProvider $provider) {}

    /**
     * @return int number of forex_rates rows written
     */
    public function sync(): int
    {
        $codes = Currency::where('is_active', true)->pluck('code')->all();

        if ($codes === []) {
            return 0;
        }

        $rates = $this->provider->fetchRates($codes);
        $today = now()->toDateString();
        $written = 0;

        foreach ($rates as $code => $data) {
            $previous = ForexRate::where('base', $code)
                ->where('quote', 'INR')
                ->where('rate_date', '<', $today)
                ->orderByDesc('rate_date')
                ->first();

            $change = $previous ? $data['rate'] - (float) $previous->rate : 0.0;
            $changePct = ($previous && (float) $previous->rate > 0)
                ? round(($change / (float) $previous->rate) * 100, 3)
                : 0.0;

            $attributes = [
                'rate' => $data['rate'],
                'change' => $change,
                'change_pct' => $changePct,
                'fetched_at' => now(),
                'source' => $data['source'],
            ];

            // See the comment in MetalRateSyncService::upsert() — same
            // 'date' cast vs. updateOrCreate array-where mismatch.
            $existing = ForexRate::where('base', $code)->where('quote', 'INR')->whereDate('rate_date', $today)->first();

            if ($existing) {
                $existing->update($attributes);
            } else {
                ForexRate::create(['base' => $code, 'quote' => 'INR', 'rate_date' => $today] + $attributes);
            }

            $written++;
        }

        return $written;
    }
}
