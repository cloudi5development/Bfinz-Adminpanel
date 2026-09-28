<?php

namespace Tests\Feature\Api;

use App\Models\Bank;
use App\Models\Currency;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SearchEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_at_least_two_characters(): void
    {
        $this->getJson('/api/v1/search?q=a')->assertStatus(422);
    }

    public function test_search_returns_matching_banks_and_currencies(): void
    {
        Bank::factory()->create(['name' => 'HDFC Bank', 'is_active' => true]);
        Bank::factory()->create(['name' => 'Axis Bank', 'is_active' => true]);
        Currency::factory()->create(['name' => 'US Dollar', 'code' => 'USD']);

        $response = $this->getJson('/api/v1/search?q=HDFC');

        $response->assertOk();
        $this->assertCount(1, $response->json('data.banks'));
        $this->assertSame('HDFC Bank', $response->json('data.banks.0.name'));
    }

    public function test_search_stores_history_only_for_authenticated_users(): void
    {
        $this->getJson('/api/v1/search?q=gold')->assertOk();
        $this->assertSame(0, SearchHistory::count());

        Sanctum::actingAs(User::factory()->create());
        $this->getJson('/api/v1/search?q=gold')->assertOk();
        $this->assertSame(1, SearchHistory::count());
    }

    public function test_recent_lists_only_the_callers_own_history(): void
    {
        $user = User::factory()->create();
        SearchHistory::create(['user_id' => $user->id, 'query' => 'gold rate']);
        SearchHistory::create(['user_id' => User::factory()->create()->id, 'query' => 'silver rate']);

        Sanctum::actingAs($user);
        $response = $this->getJson('/api/v1/search/recent');

        $response->assertOk();
        $this->assertSame(['gold rate'], $response->json('data'));
    }

    public function test_clear_recent_removes_only_the_callers_history(): void
    {
        $user = User::factory()->create();
        SearchHistory::create(['user_id' => $user->id, 'query' => 'gold rate']);
        $otherUsersHistory = SearchHistory::create(['user_id' => User::factory()->create()->id, 'query' => 'silver rate']);

        Sanctum::actingAs($user);
        $this->deleteJson('/api/v1/search/recent')->assertOk();

        $this->assertSame(0, SearchHistory::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('search_history', ['id' => $otherUsersHistory->id]);
    }

    public function test_suggestions_are_public(): void
    {
        $response = $this->getJson('/api/v1/search/suggestions');

        $response->assertOk();
        $this->assertNotEmpty($response->json('data.quick_tools'));
    }
}
