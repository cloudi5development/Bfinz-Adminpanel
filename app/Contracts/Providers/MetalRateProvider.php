<?php

namespace App\Contracts\Providers;

use App\Exceptions\ApiException;

/**
 * Source of truth for gold/silver spot prices. Implementations return the
 * spot price in INR per troy ounce (not per gram, not purity-adjusted) —
 * App\Services\Market\MetalRateCalculator does that derivation from here.
 *
 * @see docs/BUILD_SPEC.md §6 for the formula and provider notes.
 */
interface MetalRateProvider
{
    /**
     * @return array{gold: float, silver: float, source: string}
     *
     * @throws ApiException when no rate is available (the
     *                      sync job catches this, logs a failed sync_runs row, and keeps
     *                      serving the last good data rather than overwriting it)
     */
    public function fetchSpotRates(): array;
}
