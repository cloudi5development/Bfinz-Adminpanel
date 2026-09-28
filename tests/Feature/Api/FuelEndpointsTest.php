<?php

namespace Tests\Feature\Api;

use App\Models\City;
use App\Models\FuelPrice;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FuelEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_prices_returns_one_row_per_city(): void
    {
        $city = City::factory()->create();
        FuelPrice::factory()->create(['fuel' => 'petrol', 'city_id' => $city->id, 'price_date' => now()->subDay()->toDateString()]);
        FuelPrice::factory()->create(['fuel' => 'petrol', 'city_id' => $city->id, 'price_date' => now()->toDateString(), 'price_paise' => 10500]);

        $response = $this->getJson('/api/v1/fuel/prices?fuel=petrol');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(105.0, $response->json('data.0.price'));
    }

    public function test_prices_requires_a_fuel_type(): void
    {
        $this->getJson('/api/v1/fuel/prices')->assertStatus(422);
    }

    public function test_prices_filters_by_state(): void
    {
        $state = State::factory()->create();
        $cityInState = City::factory()->create(['state_id' => $state->id]);
        $cityElsewhere = City::factory()->create();

        FuelPrice::factory()->create(['fuel' => 'diesel', 'city_id' => $cityInState->id]);
        FuelPrice::factory()->create(['fuel' => 'diesel', 'city_id' => $cityElsewhere->id]);

        $response = $this->getJson("/api/v1/fuel/prices?fuel=diesel&state_id={$state->id}");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_trend_returns_points_for_a_city(): void
    {
        $city = City::factory()->create();
        FuelPrice::factory()->create(['fuel' => 'cng', 'city_id' => $city->id, 'price_date' => now()->subDays(3)->toDateString()]);

        $response = $this->getJson("/api/v1/fuel/trend?fuel=cng&city_id={$city->id}&range=7d");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_cities_lists_cities_with_a_price(): void
    {
        $city = City::factory()->create();
        City::factory()->create();
        FuelPrice::factory()->create(['fuel' => 'petrol', 'city_id' => $city->id]);

        $response = $this->getJson('/api/v1/fuel/cities?fuel=petrol');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }
}
