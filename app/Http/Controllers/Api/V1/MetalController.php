<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\Market\MetalRateResource;
use App\Http\Resources\Api\Market\TrendPointResource;
use App\Models\City;
use App\Models\MetalRate;
use App\Services\Market\MetalRateCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Reads from metal_rates (written by App\Jobs\SyncMetalRates) — never calls
 * a provider directly, per docs/BUILD_SPEC.md §4.2. `{metal}` route segment
 * is gold|silver, validated in validateMetal().
 */
class MetalController extends Controller
{
    private const METALS = ['gold', 'silver'];

    private const STALE_AFTER_HOURS = 24;

    public function __construct(private readonly MetalRateCalculator $calculator) {}

    public function live(string $metal, Request $request): JsonResponse
    {
        $metal = $this->validateMetal($metal);
        $cityId = $this->resolveCityId($request);

        $rows = $this->latestRatesFor($metal, $cityId);

        if ($rows->isEmpty()) {
            return $this->error('No rates available yet for this metal.', null, 404);
        }

        $updatedAt = $rows->max('fetched_at');

        return $this->success(
            MetalRateResource::collection($rows->values()),
            '',
            200,
            [
                'updated_at' => $updatedAt?->toIso8601String(),
                'stale' => $this->isStale($updatedAt),
            ]
        );
    }

    public function details(string $metal, Request $request): JsonResponse
    {
        $metal = $this->validateMetal($metal);
        $cityId = $this->resolveCityId($request);
        $referencePurity = $this->calculator->puritiesFor($metal)[0];

        $history = $this->recentHistory($metal, $referencePurity, $cityId);

        if ($history->isEmpty()) {
            return $this->error('No rates available yet for this metal.', null, 404);
        }

        $today = $history->first();
        $high = $history->sortByDesc('rate_per_gram_paise')->first();
        $low = $history->sortBy('rate_per_gram_paise')->first();

        return $this->success(
            [
                'today' => (new MetalRateResource($today))->resolve($request),
                'seven_day_high' => [
                    'rate' => round($high->rate_per_gram_paise / 100, 2),
                    'date' => $high->rate_date->toDateString(),
                ],
                'seven_day_low' => [
                    'rate' => round($low->rate_per_gram_paise / 100, 2),
                    'date' => $low->rate_date->toDateString(),
                ],
            ],
            '',
            200,
            [
                'updated_at' => $today->fetched_at->toIso8601String(),
                'stale' => $this->isStale($today->fetched_at),
            ]
        );
    }

    public function trend(string $metal, Request $request): JsonResponse
    {
        $metal = $this->validateMetal($metal);
        $cityId = $this->resolveCityId($request);

        $request->validate([
            'purity' => ['nullable', 'in:24k,22k,18k,999'],
            'range' => ['nullable', 'in:7d,1m,6m,1y'],
        ]);

        $purity = $request->string('purity')->value() ?: $this->calculator->puritiesFor($metal)[0];
        $since = $this->rangeStart($request->string('range')->value() ?: '1m');

        $points = MetalRate::where('metal', $metal)
            ->where('purity', $purity)
            ->where('city_id', $cityId)
            ->where('rate_date', '>=', $since->toDateString())
            ->orderBy('rate_date')
            ->get()
            ->map(fn (MetalRate $rate) => (object) [
                'date' => $rate->rate_date,
                'rate' => round($rate->rate_per_gram_paise / 100, 2),
            ]);

        return $this->success(TrendPointResource::collection($points));
    }

    public function cities(string $metal, Request $request): JsonResponse
    {
        $metal = $this->validateMetal($metal);
        $purity = $this->calculator->puritiesFor($metal)[0];

        $request->validate(['state_id' => ['nullable', 'integer', 'exists:states,id']]);

        $query = City::query();

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->integer('state_id'));
        }

        $cities = $query->get()
            ->map(function (City $city) use ($metal, $purity) {
                $rate = MetalRate::where('metal', $metal)
                    ->where('purity', $purity)
                    ->where('city_id', $city->id)
                    ->orderByDesc('rate_date')
                    ->first();

                return $rate ? [
                    'city_id' => $city->id,
                    'name' => $city->name,
                    'per_gram' => round($rate->rate_per_gram_paise / 100, 2),
                ] : null;
            })
            ->filter()
            ->values();

        return $this->success($cities);
    }

    private function validateMetal(string $metal): string
    {
        abort_unless(in_array($metal, self::METALS, true), 404, 'Unknown metal.');

        return $metal;
    }

    private function resolveCityId(Request $request): ?int
    {
        $request->validate(['city_id' => ['nullable', 'integer', 'exists:cities,id']]);

        return $request->filled('city_id') ? $request->integer('city_id') : null;
    }

    /**
     * @return Collection<int, MetalRate>
     */
    private function latestRatesFor(string $metal, ?int $cityId): Collection
    {
        $purities = $this->calculator->puritiesFor($metal);

        $rows = collect($purities)
            ->map(fn (string $purity) => MetalRate::where('metal', $metal)
                ->where('purity', $purity)
                ->where('city_id', $cityId)
                ->orderByDesc('rate_date')
                ->first())
            ->filter();

        // A city with no premium configured yet still gets a sensible
        // response, falling back to the national rate rather than a 404.
        if ($rows->isEmpty() && $cityId !== null) {
            return $this->latestRatesFor($metal, null);
        }

        return $rows;
    }

    /**
     * @return Collection<int, MetalRate>
     */
    private function recentHistory(string $metal, string $purity, ?int $cityId): Collection
    {
        $history = MetalRate::where('metal', $metal)
            ->where('purity', $purity)
            ->where('city_id', $cityId)
            ->orderByDesc('rate_date')
            ->limit(7)
            ->get();

        if ($history->isEmpty() && $cityId !== null) {
            return $this->recentHistory($metal, $purity, null);
        }

        return $history;
    }

    private function rangeStart(string $range): Carbon
    {
        return match ($range) {
            '7d' => now()->subDays(7),
            '6m' => now()->subMonths(6),
            '1y' => now()->subYear(),
            default => now()->subMonth(),
        };
    }

    private function isStale(?Carbon $updatedAt): bool
    {
        return $updatedAt === null || $updatedAt->lt(now()->subHours(self::STALE_AFTER_HOURS));
    }
}
