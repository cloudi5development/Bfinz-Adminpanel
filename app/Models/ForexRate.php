<?php

namespace App\Models;

use Database\Factories\ForexRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForexRate extends Model
{
    /** @use HasFactory<ForexRateFactory> */
    use HasFactory;

    protected $fillable = [
        'base',
        'quote',
        'rate',
        'change',
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
            'rate' => 'decimal:6',
            'change' => 'decimal:6',
            'change_pct' => 'decimal:3',
        ];
    }
}
