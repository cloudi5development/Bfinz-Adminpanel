<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_own_notifications_and_broadcasts_with_unread_count(): void
    {
        $user = User::factory()->create();
        Notification::factory()->create(['user_id' => $user->id]);
        Notification::factory()->read()->create(['user_id' => $user->id]);
        Notification::factory()->broadcast()->create();
        Notification::factory()->create(); // another user's — must not appear

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications');

        $response->assertOk();
        $this->assertCount(3, $response->json('data')); // own unread + own read + broadcast
        $this->assertSame(1, $response->json('unread_count'));
    }

    public function test_filters_by_tab_category(): void
    {
        $user = User::factory()->create();
        Notification::factory()->create(['user_id' => $user->id, 'category' => 'alerts']);
        Notification::factory()->create(['user_id' => $user->id, 'category' => 'banking']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications?tab=alerts');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_mark_read_and_delete_are_scoped_to_the_owner(): void
    {
        $owner = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs(User::factory()->create());
        $this->patchJson("/api/v1/notifications/{$notification->id}/read")->assertStatus(403);
        $this->deleteJson("/api/v1/notifications/{$notification->id}")->assertStatus(403);

        Sanctum::actingAs($owner);
        $this->patchJson("/api/v1/notifications/{$notification->id}/read")->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);

        $this->deleteJson("/api/v1/notifications/{$notification->id}")->assertOk();
        $this->assertSoftDeleted($notification);
    }

    public function test_broadcasts_cannot_be_marked_read_or_deleted_individually_yet(): void
    {
        $broadcast = Notification::factory()->broadcast()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson("/api/v1/notifications/{$broadcast->id}/read")->assertStatus(403);
        $this->deleteJson("/api/v1/notifications/{$broadcast->id}")->assertStatus(403);
    }

    public function test_mark_all_read_and_clear_all_only_touch_the_callers_own_rows(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(2)->create(['user_id' => $user->id]);
        $otherUsersNotification = Notification::factory()->create();

        Sanctum::actingAs($user);
        $this->postJson('/api/v1/notifications/read-all')->assertOk();
        $this->assertSame(0, Notification::where('user_id', $user->id)->whereNull('read_at')->count());

        $this->deleteJson('/api/v1/notifications')->assertOk();
        $this->assertSame(0, Notification::where('user_id', $user->id)->count());
        $this->assertNotSoftDeleted($otherUsersNotification);
    }
}
