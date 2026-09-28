<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\Market\FuelPriceResource;
use App\Http\Resources\Api\Market\TrendPointResource;
use App\Models\City;
use App\Models\FuelPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Reads from fuel_prices. No sync job yet — the source (admin CSV upload vs.
 * a scraper adapter, spec §13 Q3) is still an open question, so prices are
 * whatever's in the table (seeded or entered directly) rather than
 * provider-fetched. Wiring a SyncFuelPrices job later just needs to write to
 * this same table; nothing here changes.
 */
class FuelController extends Controller
{
    public function prices(Request $request): JsonResponse
    {
        $request->validate([
            'fuel' => ['required', 'in:petrol,diesel,cng'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
        ]);

        $query = FuelPrice::with('city')->where('fuel', $request->string('fuel')->value());

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->integer('city_id'));
        } elseif ($request->filled('state_id')) {
            $query->whereHas('city', fn ($q) => $q->where('state_id', $request->integer('state_id')));
        }

        $rows = $query->get()
            ->groupBy('city_id')
            ->map(fn ($rowsForCity) => $rowsForCity->sortByDesc('price_date')->first())
            ->values();

        return $this->success(FuelPriceResource::collection($rows));
    }

    public function trend(Request $request): JsonResponse
    {
        $request->validate([
            'fuel' => ['required', 'in:petrol,diesel,cng'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'range' => ['nullable', 'in:7d,1m,6m,1y'],
        ]);

        $since = $this->rangeStart($request->string('range')->value() ?: '1m');

        $points = FuelPrice::where('fuel', $request->string('fuel')->value())
            ->where('city_id', $request->integer('city_id'))
            ->where('price_date', '>=', $since->toDateString())
            ->orderBy('price_date')
            ->get()
            ->map(fn (FuelPrice $row) => (object) ['date' => $row->price_date, 'rate' => round($row->price_paise / 100, 2)]);

        return $this->success(TrendPointResource::collection($points));
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate([
            'fuel' => ['required', 'in:petrol,diesel,cng'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
        ]);

        $query = City::query();

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->integer('state_id'));
        }

        $fuel = $request->string('fuel')->value();

        $cities = $query->get()
            ->map(function (City $city) use ($fuel) {
                $row = FuelPrice::where('fuel', $fuel)->where('city_id', $city->id)->orderByDesc('price_date')->first();

                return $row ? [
                    'city_id' => $city->id,
                    'name' => $city->name,
                    'price' => round($row->price_paise / 100, 2),
                ] : null;
            })
            ->filter()
            ->values();

        return $this->success($cities);
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
}
