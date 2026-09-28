<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\Master\BankResource;
use App\Http\Resources\Api\Master\CityResource;
use App\Http\Resources\Api\Master\StateResource;
use App\Models\Bank;
use App\Models\City;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Reference data (states, cities, banks) that every other module filters
 * by. Rarely changes, so it's cached long — see the TTLs in
 * docs/BUILD_SPEC.md §7.1.
 */
class MasterController extends Controller
{
    public function states(): JsonResponse
    {
        $states = Cache::remember('master.states', now()->addHours(24), function () {
            return StateResource::collection(State::query()->orderBy('name')->get())->resolve();
        });

        return $this->success($states);
    }

    public function cities(Request $request): JsonResponse
    {
        $request->validate([
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
        ]);

        $stateId = $request->integer('state_id') ?: 'all';

        $cities = Cache::remember("master.cities.{$stateId}", now()->addHours(24), function () use ($request) {
            $query = City::query()->orderBy('name');

            if ($request->filled('state_id')) {
                $query->where('state_id', $request->integer('state_id'));
            }

            return CityResource::collection($query->get())->resolve();
        });

        return $this->success($cities);
    }

    public function banks(Request $request): JsonResponse
    {
        $request->validate([
            'category' => ['nullable', 'string', 'in:public,private,sfb,nbfc,foreign,cooperative'],
        ]);

        $category = $request->string('category')->value() ?: 'all';

        $banks = Cache::remember("master.banks.{$category}", now()->addHours(6), function () use ($request) {
            $query = Bank::query()->where('is_active', true)->orderBy('name');

            if ($request->filled('category')) {
                $query->where('category', $request->string('category')->value());
            }

            return BankResource::collection($query->get())->resolve();
        });

        return $this->success($banks);
    }
}
