<?php

namespace App\Providers\Data;

use App\Contracts\Providers\MetalRateProvider;
use App\Exceptions\ApiException;
use App\Models\Setting;

/**
 * Default metal-rate source until a real provider (Metals-API / GoldAPI.io —
 * see docs/BUILD_SPEC.md §13 Q2, still open) is chosen and funded: an admin
 * types today's spot price into the Market Rates settings page, and
 * SyncMetalRates picks it up from here on its normal schedule. Swapping in a
 * real HTTP provider later is a new class + a config binding change, nothing
 * else in the sync/API layer needs to know.
 */
class AdminManualMetalRateProvider implements MetalRateProvider
{
    public function fetchSpotRates(): array
    {
        $gold = Setting::get('market.gold_spot_inr_per_oz');
        $silver = Setting::get('market.silver_spot_inr_per_oz');

        if (! $gold || ! $silver) {
            throw new ApiException(
                'Gold/silver spot rates are not configured. Set them on the Market Rates settings page.',
                null,
                503
            );
        }

        return [
            'gold' => (float) $gold,
            'silver' => (float) $silver,
            'source' => 'admin_manual',
        ];
    }
}
