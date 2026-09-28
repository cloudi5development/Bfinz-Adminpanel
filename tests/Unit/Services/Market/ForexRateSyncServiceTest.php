<?php

namespace Tests\Unit\Services\Market;

use App\Contracts\Providers\ForexRateProvider;
use App\Models\Currency;
use App\Models\ForexRate;
use App\Services\Market\ForexRateSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ForexRateSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function serviceWithRates(array $rates): ForexRateSyncService
    {
        $provider = new class($rates) implements ForexRateProvider
        {
            public function __construct(private array $rates) {}

            public function fetchRates(array $baseCurrencyCodes): array
            {
                return $this->rates;
            }
        };

        return new ForexRateSyncService($provider);
    }

    public function test_sync_writes_one_row_per_active_currency(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_active' => true]);
        Currency::factory()->create(['code' => 'EUR', 'is_active' => true]);

        $written = $this->serviceWithRates([
            'USD' => ['rate' => 83.5, 'source' => 'fake'],
            'EUR' => ['rate' => 90.2, 'source' => 'fake'],
        ])->sync();

        $this->assertSame(2, $written);
        $this->assertSame(2, ForexRate::count());
    }

    public function test_sync_skips_inactive_currencies(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_active' => false]);

        $written = $this->serviceWithRates(['USD' => ['rate' => 83.5, 'source' => 'fake']])->sync();

        $this->assertSame(0, $written);
    }

    public function test_sync_is_idempotent_for_the_same_day(): void
    {
        Currency::factory()->create(['code' => 'USD', 'is_active' => true]);
        $service = $this->serviceWithRates(['USD' => ['rate' => 83.5, 'source' => 'fake']]);

        $service->sync();
        $service->sync();

        $this->assertSame(1, ForexRate::count());
    }

    public function test_change_is_computed_against_the_previous_days_rate(): void
    {
        Carbon::setTestNow(now());
        Currency::factory()->create(['code' => 'USD', 'is_active' => true]);

        ForexRate::create([
            'base' => 'USD',
            'quote' => 'INR',
            'rate' => 80.0,
            'change' => 0,
            'change_pct' => 0,
            'rate_date' => now()->subDay()->toDateString(),
            'fetched_at' => now()->subDay(),
            'source' => 'seed',
        ]);

        $this->serviceWithRates(['USD' => ['rate' => 83.0, 'source' => 'fake']])->sync();

        $today = ForexRate::where('base', 'USD')->whereDate('rate_date', now()->toDateString())->sole();

        $this->assertEqualsWithDelta(3.0, (float) $today->change, 0.001);
    }
}
