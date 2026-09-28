<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\City;
use App\Models\ForexRate;
use App\Models\MetalRate;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * One call renders the whole Home screen (docs/BUILD_SPEC.md §7.3). Public,
 * but personalised when a valid bearer token is present — deliberately not
 * behind auth:sanctum middleware (which would 401 a guest) — see
 * $request->user('sanctum') below.
 *
 * `best_fd` and `loan_banners` are always empty: FD/RD and Loans (P5) don't
 * exist in this repo yet. Wiring them in later is additive here.
 */
class HomeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['city_id' => ['nullable', 'integer', 'exists:cities,id']]);

        $cityId = $request->filled('city_id') ? $request->integer('city_id') : null;
        $user = $request->user('sanctum');

        $market = Cache::remember("home.market.{$cityId}", now()->addMinutes(5), fn () => $this->marketSection($cityId));

        return $this->success([
            'greeting_name' => $user?->name ?: 'Guest',
            'location' => $cityId ? City::find($cityId)?->name : null,
            ...$market,
            'quick_actions' => json_decode(Setting::get('app.quick_actions', '[]'), true) ?? [],
        ]);
    }

    private function marketSection(?int $cityId): array
    {
        $gold24k = MetalRate::where('metal', 'gold')->where('purity', '24k')->where('city_id', $cityId)->orderByDesc('rate_date')->first();
        $gold22k = MetalRate::where('metal', 'gold')->where('purity', '22k')->where('city_id', $cityId)->orderByDesc('rate_date')->first();
        $silver = MetalRate::where('metal', 'silver')->where('purity', '999')->where('city_id', $cityId)->orderByDesc('rate_date')->first();
        $usdInr = ForexRate::where('base', 'USD')->where('quote', 'INR')->orderByDesc('rate_date')->first();

        $forexTop = ForexRate::whereIn('base', function ($q) {
            $q->select('code')->from('currencies')->where('group', 'popular')->where('is_active', true);
        })->orderByDesc('rate_date')->limit(4)->get();

        return [
            'gold' => $gold24k ? [
                '24k' => round($gold24k->rate_per_gram_paise / 100, 2),
                '22k' => $gold22k ? round($gold22k->rate_per_gram_paise / 100, 2) : null,
                'change' => round($gold24k->change_paise / 100, 2),
                'updated_at' => $gold24k->fetched_at->toIso8601String(),
            ] : null,
            'silver' => $silver ? [
                '999' => round($silver->rate_per_gram_paise / 100, 2),
                'change' => round($silver->change_paise / 100, 2),
                'updated_at' => $silver->fetched_at->toIso8601String(),
            ] : null,
            'usd_inr' => $usdInr ? [
                'rate' => (float) $usdInr->rate,
                'change' => (float) $usdInr->change,
                'updated_at' => $usdInr->fetched_at->toIso8601String(),
            ] : null,
            'forex_top' => $forexTop->map(fn (ForexRate $r) => [
                'code' => $r->base,
                'rate' => (float) $r->rate,
                'change_pct' => (float) $r->change_pct,
            ])->values()->all(),
            'best_fd' => [],
            'loan_banners' => [],
        ];
    }
}
