<?php

namespace Tests\Unit\Services\Market;

use App\Services\Market\MetalRateCalculator;
use InvalidArgumentException;
use Tests\TestCase;

class MetalRateCalculatorTest extends TestCase
{
    private MetalRateCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new MetalRateCalculator;
    }

    public function test_24k_per_gram_derivation(): void
    {
        $spot = 200000.0; // INR per troy ounce

        $expected = (int) round(($spot / 31.1035) * 100);

        $this->assertSame($expected, $this->calculator->perGramPaise($spot, '24k'));
    }

    public function test_22k_is_91_point_6_percent_of_24k(): void
    {
        $spot = 200000.0;

        $rate24k = $this->calculator->perGramPaise($spot, '24k');
        $rate22k = $this->calculator->perGramPaise($spot, '22k');

        $this->assertEqualsWithDelta($rate24k * 0.916, $rate22k, 1);
    }

    public function test_18k_is_75_percent_of_24k(): void
    {
        $spot = 200000.0;

        $rate24k = $this->calculator->perGramPaise($spot, '24k');
        $rate18k = $this->calculator->perGramPaise($spot, '18k');

        $this->assertEqualsWithDelta($rate24k * 0.75, $rate18k, 1);
    }

    public function test_city_premium_is_added_on_top(): void
    {
        $spot = 200000.0;

        $withoutPremium = $this->calculator->perGramPaise($spot, '24k');
        $withPremium = $this->calculator->perGramPaise($spot, '24k', 500);

        $this->assertSame($withoutPremium + 500, $withPremium);
    }

    public function test_unknown_purity_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->perGramPaise(200000.0, '14k');
    }

    public function test_gold_has_three_purities_silver_has_one(): void
    {
        $this->assertSame(['24k', '22k', '18k'], $this->calculator->puritiesFor('gold'));
        $this->assertSame(['999'], $this->calculator->puritiesFor('silver'));
    }
}
