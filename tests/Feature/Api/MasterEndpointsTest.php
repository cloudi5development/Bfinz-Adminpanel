<?php

namespace Tests\Feature\Api;

use App\Models\Bank;
use App\Models\City;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_states_are_listed_alphabetically(): void
    {
        State::factory()->create(['name' => 'Telangana', 'code' => 'TG']);
        State::factory()->create(['name' => 'Assam', 'code' => 'AS']);

        $response = $this->getJson('/api/v1/master/states');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Assam')
            ->assertJsonPath('data.1.name', 'Telangana');
    }

    public function test_cities_can_be_filtered_by_state(): void
    {
        $maharashtra = State::factory()->create(['code' => 'MH']);
        $kerala = State::factory()->create(['code' => 'KL']);

        City::factory()->create(['state_id' => $maharashtra->id, 'name' => 'Pune']);
        City::factory()->create(['state_id' => $kerala->id, 'name' => 'Kochi']);

        $response = $this->getJson("/api/v1/master/cities?state_id={$maharashtra->id}");

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Pune'));
        $this->assertFalse($names->contains('Kochi'));
    }

    public function test_cities_rejects_an_unknown_state_id(): void
    {
        $response = $this->getJson('/api/v1/master/cities?state_id=999999');

        $response->assertStatus(422);
    }

    public function test_banks_only_returns_active_banks_and_can_filter_by_category(): void
    {
        Bank::factory()->create(['name' => 'Active Public Bank', 'category' => 'public', 'is_active' => true]);
        Bank::factory()->create(['name' => 'Active Private Bank', 'category' => 'private', 'is_active' => true]);
        Bank::factory()->inactive()->create(['name' => 'Retired Bank', 'category' => 'public']);

        $response = $this->getJson('/api/v1/master/banks?category=public');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name');

        $this->assertTrue($names->contains('Active Public Bank'));
        $this->assertFalse($names->contains('Active Private Bank'));
        $this->assertFalse($names->contains('Retired Bank'));
    }
}
