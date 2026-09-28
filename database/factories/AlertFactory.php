<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'gold',
            'asset' => '24k',
            'city_id' => null,
            'condition' => 'above',
            'target_value' => 7000,
            'is_active' => true,
            'armed' => true,
            'last_triggered_at' => null,
        ];
    }
}
