<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\State;
use Illuminate\Database\Seeder;

/**
 * A starter set of major cities per state (capital + largest metros), enough
 * to drive city pickers at launch. Admin can add more from the panel later;
 * this is intentionally not an exhaustive census list.
 */
class CitiesSeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            'AP' => ['Amaravati', 'Visakhapatnam', 'Vijayawada'],
            'AR' => ['Itanagar'],
            'AS' => ['Dispur', 'Guwahati'],
            'BR' => ['Patna', 'Gaya'],
            'CG' => ['Raipur', 'Bhilai'],
            'GA' => ['Panaji', 'Margao'],
            'GJ' => ['Gandhinagar', 'Ahmedabad', 'Surat'],
            'HR' => ['Chandigarh', 'Gurugram', 'Faridabad'],
            'HP' => ['Shimla'],
            'JH' => ['Ranchi', 'Jamshedpur'],
            'KA' => ['Bengaluru', 'Mysuru', 'Mangaluru'],
            'KL' => ['Thiruvananthapuram', 'Kochi', 'Kozhikode'],
            'MP' => ['Bhopal', 'Indore', 'Jabalpur'],
            'MH' => ['Mumbai', 'Pune', 'Nagpur', 'Nashik'],
            'MN' => ['Imphal'],
            'ML' => ['Shillong'],
            'MZ' => ['Aizawl'],
            'NL' => ['Kohima'],
            'OD' => ['Bhubaneswar', 'Cuttack'],
            'PB' => ['Chandigarh', 'Ludhiana', 'Amritsar'],
            'RJ' => ['Jaipur', 'Jodhpur', 'Udaipur'],
            'SK' => ['Gangtok'],
            'TN' => ['Chennai', 'Coimbatore', 'Madurai'],
            'TG' => ['Hyderabad', 'Warangal'],
            'TR' => ['Agartala'],
            'UP' => ['Lucknow', 'Kanpur', 'Noida', 'Ghaziabad'],
            'UK' => ['Dehradun'],
            'WB' => ['Kolkata', 'Howrah', 'Siliguri'],
            'AN' => ['Port Blair'],
            'CH' => ['Chandigarh'],
            'DN' => ['Daman', 'Silvassa'],
            'DL' => ['New Delhi'],
            'JK' => ['Srinagar', 'Jammu'],
            'LA' => ['Leh'],
            'LD' => ['Kavaratti'],
            'PY' => ['Puducherry'],
        ];

        $states = State::query()->pluck('id', 'code');

        foreach ($cities as $stateCode => $names) {
            $stateId = $states[$stateCode] ?? null;

            if ($stateId === null) {
                continue;
            }

            foreach ($names as $name) {
                City::query()->updateOrCreate(
                    ['state_id' => $stateId, 'name' => $name],
                );
            }
        }
    }
}
