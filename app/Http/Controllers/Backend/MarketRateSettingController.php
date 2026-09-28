<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Today's gold/silver spot price (INR per troy ounce), read by
 * App\Providers\Data\AdminManualMetalRateProvider — the placeholder metal
 * rate source until a real provider is chosen (docs/BUILD_SPEC.md §13 Q2).
 * App\Jobs\SyncMetalRates picks up whatever's saved here on its normal
 * schedule; nothing needs to be re-run manually after saving.
 */
class MarketRateSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        // Form fields are market[gold_spot_inr_per_oz] etc (nested array
        // syntax) rather than a literal dot in the field name — PHP silently
        // rewrites dots to underscores in POSTed field names, so a flat
        // "market.gold_spot_inr_per_oz" input name would never actually
        // reach the request under that key.
        $validated = $request->validate([
            'market.gold_spot_inr_per_oz' => ['required', 'numeric', 'min:0'],
            'market.silver_spot_inr_per_oz' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::putMany([
            'market.gold_spot_inr_per_oz' => $validated['market']['gold_spot_inr_per_oz'],
            'market.silver_spot_inr_per_oz' => $validated['market']['silver_spot_inr_per_oz'],
        ]);

        return back()->with('success', 'Market rates saved.');
    }
}
