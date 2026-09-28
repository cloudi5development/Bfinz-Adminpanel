<?php

namespace Database\Factories;

use App\Models\MetalRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetalRate>
 */
class MetalRateFactory extends Factory
{
    protected $model = MetalRate::class;

    public function definition(): array
    {
        return [
            'metal' => 'gold',
            'purity' => '24k',
            'city_id' => null,
            'rate_per_gram_paise' => fake()->numberBetween(600000, 800000),
            'change_paise' => fake()->numberBetween(-2000, 2000),
            'change_pct' => fake()->randomFloat(3, -2, 2),
            'rate_date' => now()->toDateString(),
            'fetched_at' => now(),
            'source' => 'test',
        ];
    }
}
