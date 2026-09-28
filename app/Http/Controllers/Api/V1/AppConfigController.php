<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

/**
 * Single call the app makes at launch to get its remote-config knobs.
 * Values live in the generic `settings` key/value store (App\Models\Setting)
 * so admins can change them without a release; see docs/BUILD_SPEC.md §7.1.
 */
class AppConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $config = Cache::remember('app.config', now()->addMinutes(10), function () {
            return [
                'min_version' => Setting::get('app.min_version', '1.0.0'),
                'force_update' => (bool) Setting::get('app.force_update', false),
                'quick_actions' => json_decode(Setting::get('app.quick_actions', '[]'), true) ?? [],
                'banners' => json_decode(Setting::get('app.banners', '[]'), true) ?? [],
            ];
        });

        return $this->success($config);
    }
}
