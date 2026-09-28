<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Requests\Api\AlertRequest;
use App\Http\Resources\Api\Alerts\AlertResource;
use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * All endpoints are auth:sanctum and scoped to the caller's own alerts —
 * see routes/api.php. Shared by gold/silver/forex/fuel/fd/rd per
 * docs/BUILD_SPEC.md §7.6 (fd/rd accepted by validation but never fire
 * yet, since those products don't exist until P5).
 */
class AlertController extends Controller
{
    private const MAX_ACTIVE_ALERTS = 20;

    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->alerts()->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->value());
        }

        return $this->success(AlertResource::collection($query->get()));
    }

    public function store(AlertRequest $request): JsonResponse
    {
        $activeCount = $request->user()->alerts()->where('is_active', true)->count();

        if ($activeCount >= self::MAX_ACTIVE_ALERTS) {
            return $this->error('You can have at most '.self::MAX_ACTIVE_ALERTS.' active alerts. Delete or disable one first.', null, 422);
        }

        $alert = $request->user()->alerts()->create($request->validated() + ['armed' => true]);

        return $this->success(new AlertResource($alert), 'Alert created.', 201);
    }

    public function update(AlertRequest $request, Alert $alert): JsonResponse
    {
        $this->authorizeOwner($request, $alert);

        $alert->update($request->validated());

        return $this->success(new AlertResource($alert->fresh()), 'Alert updated.');
    }

    public function destroy(Request $request, Alert $alert): JsonResponse
    {
        $this->authorizeOwner($request, $alert);

        $alert->delete();

        return $this->success(null, 'Alert deleted.');
    }

    private function authorizeOwner(Request $request, Alert $alert): void
    {
        abort_unless($alert->user_id === $request->user()->id, 403, 'This action is unauthorized.');
    }
}
