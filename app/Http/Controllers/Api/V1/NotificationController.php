<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\Api\Alerts\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A notification with user_id = null is a broadcast, visible to everyone —
 * but this pass has no per-user read/delete state for broadcasts (that
 * needs a pivot table; deferred until the admin "send broadcast" feature
 * is actually built, P8). They're listed and counted as always-unread here,
 * and can't be marked read or deleted individually yet.
 */
class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['tab' => ['nullable', 'in:all,alerts,banking,loans']]);

        $userId = $request->user()->id;
        $tab = $request->string('tab')->value() ?: 'all';

        $query = Notification::query()
            ->where(fn ($q) => $q->where('user_id', $userId)->orWhereNull('user_id'))
            ->latest();

        if ($tab !== 'all') {
            $query->where('category', $tab);
        }

        $notifications = $query->paginate($request->integer('per_page', 20));

        $unreadCount = Notification::where('user_id', $userId)->whereNull('read_at')->count();

        return $this->success(
            NotificationResource::collection($notifications),
            '',
            200,
            ['unread_count' => $unreadCount]
        );
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        $this->authorizeOwner($request, $notification);

        $notification->update(['read_at' => now()]);

        return $this->success(null, 'Notification marked as read.');
    }

    public function markAllRead(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return $this->success(null, 'All notifications marked as read.');
    }

    public function destroy(Request $request, Notification $notification): JsonResponse
    {
        $this->authorizeOwner($request, $notification);

        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }

    public function destroyAll(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)->delete();

        return $this->success(null, 'All notifications cleared.');
    }

    private function authorizeOwner(Request $request, Notification $notification): void
    {
        if ($notification->user_id === null) {
            abort(403, 'Broadcast notifications cannot be modified individually yet.');
        }

        abort_unless($notification->user_id === $request->user()->id, 403, 'This action is unauthorized.');
    }
}
