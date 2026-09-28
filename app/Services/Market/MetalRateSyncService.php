<?php

namespace App\Services\Market;

use App\Contracts\Providers\MetalRateProvider;
use App\Models\MetalCityPremium;
use App\Models\MetalRate;

/**
 * Writes today's metal_rates rows from the bound MetalRateProvider. Keyed
 * upserts (metal, purity, city_id, rate_date) make re-running this for the
 * same day idempotent — a retry recomputes the same inputs into the same
 * row instead of appending a duplicate.
 */
class MetalRateSyncService
{
    public function __construct(
        private readonly MetalRateProvider $provider,
        private readonly MetalRateCalculator $calculator,
    ) {}

    /**
     * @return int number of metal_rates rows written
     */
    public function sync(): int
    {
        $spot = $this->provider->fetchSpotRates();
        $today = now()->toDateString();
        $written = 0;

        foreach (['gold', 'silver'] as $metal) {
            $spotInrPerOz = $spot[$metal];
            $purities = $this->calculator->puritiesFor($metal);

            foreach ($purities as $purity) {
                $written += $this->upsert($metal, $purity, null, $spotInrPerOz, 0, $today, $spot['source']);
            }

            MetalCityPremium::where('metal', $metal)->get()->each(function (MetalCityPremium $premium) use (&$written, $metal, $purities, $spotInrPerOz, $today, $spot) {
                foreach ($purities as $purity) {
                    $written += $this->upsert($metal, $purity, $premium->city_id, $spotInrPerOz, $premium->premium_paise, $today, $spot['source']);
                }
            });
        }

        return $written;
    }

    private function upsert(
        string $metal,
        string $purity,
        ?int $cityId,
        float $spotInrPerOz,
        int $cityPremiumPaise,
        string $date,
        string $source,
    ): int {
        $ratePaise = $this->calculator->perGramPaise($spotInrPerOz, $purity, $cityPremiumPaise);

        $previous = MetalRate::where('metal', $metal)
            ->where('purity', $purity)
            ->where('city_id', $cityId)
            ->where('rate_date', '<', $date)
            ->orderByDesc('rate_date')
            ->first();

        $changePaise = $previous ? $ratePaise - $previous->rate_per_gram_paise : 0;
        $changePct = ($previous && $previous->rate_per_gram_paise > 0)
            ? round(($changePaise / $previous->rate_per_gram_paise) * 100, 3)
            : 0.0;

        $attributes = [
            'rate_per_gram_paise' => $ratePaise,
            'change_paise' => $changePaise,
            'change_pct' => $changePct,
            'fetched_at' => now(),
            'source' => $source,
        ];

        // Not updateOrCreate(): its array-where does a literal string match
        // against 'rate_date', but the 'date' cast serializes to
        // 'Y-m-d H:i:s' on write — a bare 'Y-m-d' search value never matches
        // the row just created, and silently doubles up (city_id being
        // nullable hides it from the unique index too). whereDate() compares
        // only the date part, however it's stored.
        $existing = MetalRate::where('metal', $metal)
            ->where('purity', $purity)
            ->where('city_id', $cityId)
            ->whereDate('rate_date', $date)
            ->first();

        if ($existing) {
            $existing->update($attributes);
        } else {
            MetalRate::create(['metal' => $metal, 'purity' => $purity, 'city_id' => $cityId, 'rate_date' => $date] + $attributes);
        }

        return 1;
    }
}
