<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\FuelPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelPrice>
 */
class FuelPriceFactory extends Factory
{
    protected $model = FuelPrice::class;

    public function definition(): array
    {
        return [
            'fuel' => 'petrol',
            'city_id' => City::factory(),
            'price_paise' => fake()->numberBetween(9000, 11000),
            'change_paise' => fake()->numberBetween(-50, 50),
            'price_date' => now()->toDateString(),
            'source' => 'test',
        ];
    }
}
