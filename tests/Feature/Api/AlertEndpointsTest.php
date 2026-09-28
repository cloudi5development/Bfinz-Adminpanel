<?php

namespace Tests\Feature\Api;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AlertEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_alerts(): void
    {
        $this->getJson('/api/v1/alerts')->assertStatus(401);
    }

    public function test_user_can_create_and_list_their_alerts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/alerts', [
            'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 7000,
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/alerts');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_creating_an_alert_beyond_the_active_limit_is_rejected(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Alert::factory()->count(20)->create(['user_id' => $user->id, 'is_active' => true]);

        $this->postJson('/api/v1/alerts', [
            'type' => 'silver', 'asset' => '999', 'condition' => 'below', 'target_value' => 100,
        ])->assertStatus(422);
    }

    public function test_a_user_cannot_update_or_delete_another_users_alert(): void
    {
        $owner = User::factory()->create();
        $alert = Alert::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/v1/alerts/{$alert->id}", [
            'type' => 'gold', 'asset' => '24k', 'condition' => 'above', 'target_value' => 1,
        ])->assertStatus(403);

        $this->deleteJson("/api/v1/alerts/{$alert->id}")->assertStatus(403);
    }

    public function test_owner_can_update_and_delete_their_alert(): void
    {
        $user = User::factory()->create();
        $alert = Alert::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->putJson("/api/v1/alerts/{$alert->id}", [
            'type' => 'gold', 'asset' => '22k', 'condition' => 'below', 'target_value' => 6000,
        ])->assertOk()->assertJsonPath('data.asset', '22k');

        $this->deleteJson("/api/v1/alerts/{$alert->id}")->assertOk();
        $this->assertModelMissing($alert);
    }

    public function test_any_change_condition_does_not_require_a_target_value(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/alerts', [
            'type' => 'gold', 'asset' => '24k', 'condition' => 'any_change',
        ])->assertStatus(201);
    }
}
