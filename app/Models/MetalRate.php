<?php

namespace App\Models;

use Database\Factories\MetalRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetalRate extends Model
{
    /** @use HasFactory<MetalRateFactory> */
    use HasFactory;

    protected $fillable = [
        'metal',
        'purity',
        'city_id',
        'rate_per_gram_paise',
        'change_paise',
        'change_pct',
        'rate_date',
        'fetched_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rate_date' => 'date',
            'fetched_at' => 'datetime',
            'change_pct' => 'decimal:3',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
