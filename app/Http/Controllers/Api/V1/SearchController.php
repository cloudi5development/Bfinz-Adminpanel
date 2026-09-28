<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\SearchHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * docs/BUILD_SPEC.md §7.4 groups results across tools/banks/ifsc/loans/
 * deposits/articles — only `banks` and `currencies` exist as real modules
 * in this repo so far (Master data, P0; Forex, P2). The other groups are
 * wired in as their modules land (IFSC/locator P9, loans/deposits P5,
 * articles P8) — same shape, just more `->when()` branches below.
 */
class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);

        $q = $request->string('q')->value();

        if ($request->user('sanctum')) {
            SearchHistory::create(['user_id' => $request->user('sanctum')->id, 'query' => $q]);
        }

        return $this->success([
            'banks' => Bank::where('is_active', true)->where('name', 'like', "%{$q}%")->limit(10)->get(['id', 'name', 'short_name'])->map(fn (Bank $b) => [
                'id' => $b->id, 'name' => $b->name, 'short_name' => $b->short_name,
            ]),
            'currencies' => Currency::where('is_active', true)
                ->where(fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%"))
                ->limit(10)->get(['code', 'name']),
        ]);
    }

    public function recent(Request $request): JsonResponse
    {
        $recent = SearchHistory::where('user_id', $request->user()->id)
            ->latest('created_at')
            ->limit(10)
            ->pluck('query');

        return $this->success($recent);
    }

    public function clearRecent(Request $request): JsonResponse
    {
        SearchHistory::where('user_id', $request->user()->id)->delete();

        return $this->success(null, 'Search history cleared.');
    }

    /**
     * Static for now — a real "quick tools" list depends on Finance Tools
     * (P7) and the calculator suite existing. Update this once they do.
     */
    public function suggestions(): JsonResponse
    {
        return $this->success([
            'quick_tools' => ['Gold Rate', 'Silver Rate', 'Forex Rates', 'Fuel Price'],
            'categories' => ['Banks', 'Currencies'],
        ]);
    }
}
