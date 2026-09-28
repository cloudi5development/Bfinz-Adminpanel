<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetalCityPremium extends Model
{
    // Eloquent's pluralizer turns "MetalCityPremium" into "metal_city_premia"
    // (Latin plural of "premium") — pin the table name explicitly.
    protected $table = 'metal_city_premiums';

    protected $fillable = [
        'city_id',
        'metal',
        'premium_paise',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
