<?php

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bank>
 */
class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Bank';

        return [
            'name' => $name,
            'short_name' => fake()->unique()->lexify('????'),
            'category' => fake()->randomElement(['public', 'private', 'sfb', 'nbfc', 'foreign', 'cooperative']),
            'logo_url' => null,
            'rating' => fake()->randomFloat(1, 2.5, 5),
            'website' => fake()->url(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
