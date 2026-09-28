<?php

namespace Tests\Feature\Api;

use App\Models\Currency;
use App\Models\ForexRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForexEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_rates_lists_active_currencies_filtered_by_group(): void
    {
        Currency::factory()->create(['code' => 'USD', 'group' => 'popular', 'is_active' => true]);
        Currency::factory()->create(['code' => 'JPY', 'group' => 'asia', 'is_active' => true]);
        ForexRate::factory()->create(['base' => 'USD', 'rate' => 83.5]);
        ForexRate::factory()->create(['base' => 'JPY', 'rate' => 0.56]);

        $response = $this->getJson('/api/v1/forex/rates?group=popular');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code');
        $this->assertTrue($codes->contains('USD'));
        $this->assertFalse($codes->contains('JPY'));
    }

    public function test_show_returns_current_rate_and_history(): void
    {
        ForexRate::factory()->create(['base' => 'USD', 'rate' => 83.5, 'rate_date' => now()->toDateString()]);
        ForexRate::factory()->create(['base' => 'USD', 'rate' => 82.9, 'rate_date' => now()->subDays(2)->toDateString()]);

        $response = $this->getJson('/api/v1/forex/USD?range=7d');

        $response->assertOk();
        $this->assertSame('USD', $response->json('data.current.code'));
        $this->assertCount(2, $response->json('data.history'));
    }

    public function test_show_returns_404_for_a_currency_with_no_rate(): void
    {
        $this->getJson('/api/v1/forex/ZZZ')->assertStatus(404);
    }

    public function test_movers_ranks_by_change_percentage(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_active' => true]);
        Currency::factory()->create(['code' => 'GBP', 'is_active' => true]);
        ForexRate::factory()->create(['base' => 'USD', 'change_pct' => 2.5]);
        ForexRate::factory()->create(['base' => 'GBP', 'change_pct' => -1.2]);

        $response = $this->getJson('/api/v1/forex/movers');

        $response->assertOk();
        $this->assertSame('USD', $response->json('data.top_gainers.0.code'));
        $this->assertSame('GBP', $response->json('data.top_losers.0.code'));
    }

    public function test_currencies_lists_only_active_ones(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_active' => true]);
        Currency::factory()->create(['code' => 'AED', 'is_active' => false]);

        $response = $this->getJson('/api/v1/forex/currencies');

        $response->assertOk();
        $codes = collect($response->json('data'))->pluck('code');
        $this->assertTrue($codes->contains('USD'));
        $this->assertFalse($codes->contains('AED'));
    }
}
