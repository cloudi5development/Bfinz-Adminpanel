<?php

namespace Tests\Feature\Api;

use App\Models\City;
use App\Models\Currency;
use App\Models\ForexRate;
use App\Models\MetalRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HomeEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_market_data_for_a_guest(): void
    {
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'city_id' => null, 'rate_per_gram_paise' => 700000]);
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '22k', 'city_id' => null, 'rate_per_gram_paise' => 641200]);
        MetalRate::factory()->create(['metal' => 'silver', 'purity' => '999', 'city_id' => null]);
        ForexRate::factory()->create(['base' => 'USD', 'rate' => 83.5]);

        $response = $this->getJson('/api/v1/home');

        $response->assertOk();
        $this->assertSame('Guest', $response->json('data.greeting_name'));
        $this->assertEquals(7000.0, $response->json('data.gold.24k'));
        $this->assertNotNull($response->json('data.usd_inr'));
        $this->assertSame([], $response->json('data.best_fd'));
    }

    public function test_home_personalises_the_greeting_when_authenticated(): void
    {
        $user = User::factory()->create(['name' => 'Asha']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/home');

        $response->assertOk();
        $this->assertSame('Asha', $response->json('data.greeting_name'));
    }

    public function test_home_includes_the_city_name_when_city_id_given(): void
    {
        $city = City::factory()->create(['name' => 'Pune']);

        $response = $this->getJson("/api/v1/home?city_id={$city->id}");

        $response->assertOk();
        $this->assertSame('Pune', $response->json('data.location'));
    }

    public function test_home_rejects_an_unknown_city_id(): void
    {
        $this->getJson('/api/v1/home?city_id=999999')->assertStatus(422);
    }

    public function test_forex_top_only_includes_the_popular_group(): void
    {
        Currency::factory()->create(['code' => 'USD', 'group' => 'popular', 'is_active' => true]);
        Currency::factory()->create(['code' => 'JPY', 'group' => 'asia', 'is_active' => true]);
        ForexRate::factory()->create(['base' => 'USD']);
        ForexRate::factory()->create(['base' => 'JPY']);

        $response = $this->getJson('/api/v1/home');

        $codes = collect($response->json('data.forex_top'))->pluck('code');
        $this->assertTrue($codes->contains('USD'));
        $this->assertFalse($codes->contains('JPY'));
    }
}
