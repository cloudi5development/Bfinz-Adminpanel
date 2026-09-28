<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\Market\CurrencyResource;
use App\Http\Resources\Api\Market\ForexRateResource;
use App\Http\Resources\Api\Market\TrendPointResource;
use App\Models\Currency;
use App\Models\ForexRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ForexController extends Controller
{
    private const STALE_AFTER_HOURS = 24;

    public function rates(Request $request): JsonResponse
    {
        $request->validate([
            'group' => ['nullable', 'in:all,popular,asia,middle_east,europe,americas,other'],
        ]);

        $group = $request->string('group')->value() ?: 'all';

        $query = Currency::where('is_active', true);

        if ($group !== 'all') {
            $query->where('group', $group);
        }

        $rows = $this->latestRatesFor($query->pluck('code'));

        return $this->success(
            ForexRateResource::collection($rows),
            '',
            200,
            [
                'updated_at' => $rows->max('fetched_at')?->toIso8601String(),
                'stale' => $this->isStale($rows->max('fetched_at')),
            ]
        );
    }

    public function show(string $code, Request $request): JsonResponse
    {
        $code = strtoupper($code);

        $request->validate(['range' => ['nullable', 'in:7d,1m,6m,1y']]);

        $latest = ForexRate::where('base', $code)->where('quote', 'INR')->orderByDesc('rate_date')->first();

        if (! $latest) {
            return $this->error('No rate available for this currency.', null, 404);
        }

        $since = $this->rangeStart($request->string('range')->value() ?: '1m');

        $history = ForexRate::where('base', $code)
            ->where('quote', 'INR')
            ->where('rate_date', '>=', $since->toDateString())
            ->orderBy('rate_date')
            ->get()
            ->map(fn (ForexRate $rate) => (object) ['date' => $rate->rate_date, 'rate' => (float) $rate->rate]);

        return $this->success(
            [
                'current' => (new ForexRateResource($latest))->resolve($request),
                'history' => TrendPointResource::collection($history)->resolve($request),
            ],
            '',
            200,
            [
                'updated_at' => $latest->fetched_at->toIso8601String(),
                'stale' => $this->isStale($latest->fetched_at),
            ]
        );
    }

    public function movers(): JsonResponse
    {
        $codes = Currency::where('is_active', true)->pluck('code');
        $rows = $this->latestRatesFor($codes)->sortByDesc('change_pct')->values();

        return $this->success([
            'top_gainers' => ForexRateResource::collection($rows->take(5))->resolve(),
            'top_losers' => ForexRateResource::collection($rows->reverse()->take(5)->values())->resolve(),
        ]);
    }

    public function currencies(): JsonResponse
    {
        $currencies = Cache::remember('forex.currencies', now()->addHours(24), function () {
            return CurrencyResource::collection(
                Currency::where('is_active', true)->orderBy('group')->orderBy('name')->get()
            )->resolve();
        });

        return $this->success($currencies);
    }

    /**
     * @param  Collection<int, string>  $codes
     * @return Collection<int, ForexRate>
     */
    private function latestRatesFor($codes)
    {
        return $codes
            ->map(fn (string $code) => ForexRate::where('base', $code)->where('quote', 'INR')->orderByDesc('rate_date')->first())
            ->filter()
            ->values();
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
