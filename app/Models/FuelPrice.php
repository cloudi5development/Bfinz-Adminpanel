<?php

namespace App\Models;

use Database\Factories\FuelPriceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelPrice extends Model
{
    /** @use HasFactory<FuelPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'fuel',
        'city_id',
        'price_paise',
        'change_paise',
        'price_date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'price_date' => 'date',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
