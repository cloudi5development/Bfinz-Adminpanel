<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // The account that signs in to the admin panel.
        $this->call(AdminUserSeeder::class);

        // Bfinz masters: states/cities/banks power the /master/* endpoints
        // and every module's location/bank filters.
        $this->call([
            StatesSeeder::class,
            CitiesSeeder::class,
            BanksSeeder::class,
            CurrenciesSeeder::class,
        ]);
    }
}
