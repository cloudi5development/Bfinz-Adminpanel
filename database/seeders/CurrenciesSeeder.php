<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'country' => 'United States', 'group' => 'popular'],
            ['code' => 'EUR', 'name' => 'Euro', 'country' => 'Eurozone', 'group' => 'popular'],
            ['code' => 'GBP', 'name' => 'British Pound', 'country' => 'United Kingdom', 'group' => 'popular'],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'country' => 'Australia', 'group' => 'popular'],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'country' => 'Canada', 'group' => 'americas'],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'country' => 'Japan', 'group' => 'asia'],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'country' => 'China', 'group' => 'asia'],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'country' => 'Singapore', 'group' => 'asia'],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'country' => 'Malaysia', 'group' => 'asia'],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'country' => 'Switzerland', 'group' => 'europe'],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'country' => 'Sweden', 'group' => 'europe'],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'country' => 'Norway', 'group' => 'europe'],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'country' => 'Brazil', 'group' => 'americas'],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'country' => 'Mexico', 'group' => 'americas'],
            // Gulf currencies (AED/SAR/QAR) are pegged to USD and not
            // published by ECB/Frankfurter — see the gap noted in
            // FrankfurterForexRateProvider. Seeded inactive until a provider
            // that covers them is wired up, so sync doesn't fail on them.
            ['code' => 'AED', 'name' => 'UAE Dirham', 'country' => 'United Arab Emirates', 'group' => 'middle_east', 'is_active' => false],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'country' => 'Saudi Arabia', 'group' => 'middle_east', 'is_active' => false],
            ['code' => 'QAR', 'name' => 'Qatari Riyal', 'country' => 'Qatar', 'group' => 'middle_east', 'is_active' => false],
        ];

        foreach ($currencies as $currency) {
            Currency::query()->updateOrCreate(
                ['code' => $currency['code']],
                $currency + ['is_active' => true]
            );
        }
    }
}
