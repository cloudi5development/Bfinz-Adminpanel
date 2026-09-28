<?php

namespace Tests\Feature\Api;

use App\Models\City;
use App\Models\MetalRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MetalEndpointsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_live_returns_all_purities_for_a_metal(): void
    {
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_per_gram_paise' => 700000]);
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '22k', 'rate_per_gram_paise' => 641200]);
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '18k', 'rate_per_gram_paise' => 525000]);

        $response = $this->getJson('/api/v1/metals/gold/live');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
        // json_encode drops the trailing .0 for whole numbers, so a strict
        // assertSame would fail on type (int 7000 vs float 7000.0) even
        // though the value is correct.
        $this->assertEquals(7000.0, $response->json('data.0.per_gram'));
        $this->assertNotNull($response->json('updated_at'));
        $this->assertFalse($response->json('stale'));
    }

    public function test_live_rejects_an_unknown_metal(): void
    {
        $this->getJson('/api/v1/metals/platinum/live')->assertStatus(404);
    }

    public function test_live_flags_stale_when_the_last_sync_is_old(): void
    {
        Carbon::setTestNow(now());

        MetalRate::factory()->create([
            'metal' => 'silver',
            'purity' => '999',
            'fetched_at' => now()->subHours(30),
            'rate_date' => now()->subDays(2)->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/metals/silver/live');

        $response->assertOk();
        $this->assertTrue($response->json('stale'));
    }

    public function test_live_falls_back_to_national_rate_when_city_has_no_premium(): void
    {
        $city = City::factory()->create();
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'city_id' => null]);

        $response = $this->getJson("/api/v1/metals/gold/live?city_id={$city->id}");

        $response->assertOk();
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_trend_returns_points_within_the_requested_range(): void
    {
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_date' => now()->subDays(3)->toDateString()]);
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_date' => now()->subDays(40)->toDateString()]);

        $response = $this->getJson('/api/v1/metals/gold/trend?purity=24k&range=7d');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_cities_lists_only_cities_with_a_configured_rate(): void
    {
        $withRate = City::factory()->create();
        City::factory()->create();

        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'city_id' => $withRate->id]);

        $response = $this->getJson('/api/v1/metals/gold/cities');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($withRate->id, $response->json('data.0.city_id'));
    }
}
