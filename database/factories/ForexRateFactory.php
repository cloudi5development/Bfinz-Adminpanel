<?php

namespace Database\Factories;

use App\Models\ForexRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForexRate>
 */
class ForexRateFactory extends Factory
{
    protected $model = ForexRate::class;

    public function definition(): array
    {
        return [
            'base' => 'USD',
            'quote' => 'INR',
            'rate' => fake()->randomFloat(4, 80, 90),
            'change' => fake()->randomFloat(4, -1, 1),
            'change_pct' => fake()->randomFloat(3, -1, 1),
            'rate_date' => now()->toDateString(),
            'fetched_at' => now(),
            'source' => 'test',
        ];
    }
}
