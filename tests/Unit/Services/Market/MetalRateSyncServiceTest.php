<?php

namespace Tests\Unit\Services\Market;

use App\Contracts\Providers\MetalRateProvider;
use App\Exceptions\ApiException;
use App\Models\City;
use App\Models\MetalCityPremium;
use App\Models\MetalRate;
use App\Services\Market\MetalRateCalculator;
use App\Services\Market\MetalRateSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MetalRateSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function serviceWithSpotRates(float $gold, float $silver): MetalRateSyncService
    {
        $provider = new class($gold, $silver) implements MetalRateProvider
        {
            public function __construct(private float $gold, private float $silver) {}

            public function fetchSpotRates(): array
            {
                return ['gold' => $this->gold, 'silver' => $this->silver, 'source' => 'fake'];
            }
        };

        return new MetalRateSyncService($provider, new MetalRateCalculator);
    }

    public function test_sync_writes_the_national_rows_for_every_purity(): void
    {
        $written = $this->serviceWithSpotRates(200000, 250000)->sync();

        // 3 gold purities + 1 silver purity, national (no city) only.
        $this->assertSame(4, $written);
        $this->assertSame(4, MetalRate::count());
        $this->assertSame(3, MetalRate::where('metal', 'gold')->count());
        $this->assertSame(1, MetalRate::where('metal', 'silver')->count());
    }

    public function test_sync_is_idempotent_for_the_same_day(): void
    {
        $service = $this->serviceWithSpotRates(200000, 250000);

        $service->sync();
        $countAfterFirst = MetalRate::count();
        $service->sync();
        $countAfterSecond = MetalRate::count();

        $this->assertSame($countAfterFirst, $countAfterSecond);
    }

    public function test_sync_writes_a_row_per_city_premium(): void
    {
        $city = City::factory()->create();
        MetalCityPremium::create(['city_id' => $city->id, 'metal' => 'gold', 'premium_paise' => 1000]);

        $this->serviceWithSpotRates(200000, 250000)->sync();

        $national = MetalRate::where('metal', 'gold')->where('purity', '24k')->whereNull('city_id')->sole();
        $cityRate = MetalRate::where('metal', 'gold')->where('purity', '24k')->where('city_id', $city->id)->sole();

        $this->assertSame($national->rate_per_gram_paise + 1000, $cityRate->rate_per_gram_paise);
    }

    public function test_change_is_computed_against_the_previous_days_rate(): void
    {
        Carbon::setTestNow(now());

        MetalRate::create([
            'metal' => 'gold',
            'purity' => '24k',
            'city_id' => null,
            'rate_per_gram_paise' => 600000,
            'change_paise' => 0,
            'change_pct' => 0,
            'rate_date' => now()->subDay()->toDateString(),
            'fetched_at' => now()->subDay(),
            'source' => 'seed',
        ]);

        $this->serviceWithSpotRates(200000, 250000)->sync();

        $today = MetalRate::where('metal', 'gold')->where('purity', '24k')->whereNull('city_id')->whereDate('rate_date', now()->toDateString())->sole();

        $this->assertSame($today->rate_per_gram_paise - 600000, $today->change_paise);
    }

    public function test_provider_failure_propagates_without_touching_existing_data(): void
    {
        MetalRate::factory()->create(['metal' => 'gold', 'purity' => '24k', 'rate_date' => now()->subDay()->toDateString()]);

        $provider = new class implements MetalRateProvider
        {
            public function fetchSpotRates(): array
            {
                throw new ApiException('Provider unavailable.', null, 503);
            }
        };

        $service = new MetalRateSyncService($provider, new MetalRateCalculator);

        $this->expectException(ApiException::class);

        try {
            $service->sync();
        } finally {
            $this->assertSame(1, MetalRate::count());
        }
    }
}
