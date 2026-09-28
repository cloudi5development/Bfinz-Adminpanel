<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

/**
 * Launch coverage: top public/private banks + a couple of small finance
 * banks. Spec §13 Q5 (exact launch bank list) is still open with Pavi —
 * this list can be trimmed or extended from the admin panel without a
 * migration once that's confirmed.
 */
class BanksSeeder extends Seeder
{
    public function run(): void
    {
        $banks = [
            ['name' => 'State Bank of India', 'short_name' => 'SBI', 'category' => 'public'],
            ['name' => 'Punjab National Bank', 'short_name' => 'PNB', 'category' => 'public'],
            ['name' => 'Bank of Baroda', 'short_name' => 'BoB', 'category' => 'public'],
            ['name' => 'Canara Bank', 'short_name' => 'Canara', 'category' => 'public'],
            ['name' => 'Union Bank of India', 'short_name' => 'Union Bank', 'category' => 'public'],
            ['name' => 'Bank of India', 'short_name' => 'BoI', 'category' => 'public'],
            ['name' => 'Indian Bank', 'short_name' => 'Indian Bank', 'category' => 'public'],
            ['name' => 'HDFC Bank', 'short_name' => 'HDFC', 'category' => 'private'],
            ['name' => 'ICICI Bank', 'short_name' => 'ICICI', 'category' => 'private'],
            ['name' => 'Axis Bank', 'short_name' => 'Axis', 'category' => 'private'],
            ['name' => 'Kotak Mahindra Bank', 'short_name' => 'Kotak', 'category' => 'private'],
            ['name' => 'IndusInd Bank', 'short_name' => 'IndusInd', 'category' => 'private'],
            ['name' => 'IDFC FIRST Bank', 'short_name' => 'IDFC First', 'category' => 'private'],
            ['name' => 'Yes Bank', 'short_name' => 'Yes Bank', 'category' => 'private'],
            ['name' => 'Federal Bank', 'short_name' => 'Federal', 'category' => 'private'],
            ['name' => 'RBL Bank', 'short_name' => 'RBL', 'category' => 'private'],
            ['name' => 'AU Small Finance Bank', 'short_name' => 'AU SFB', 'category' => 'sfb'],
            ['name' => 'Equitas Small Finance Bank', 'short_name' => 'Equitas SFB', 'category' => 'sfb'],
        ];

        foreach ($banks as $bank) {
            Bank::query()->updateOrCreate(
                ['short_name' => $bank['short_name']],
                $bank + ['is_active' => true]
            );
        }
    }
}
