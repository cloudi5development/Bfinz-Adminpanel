<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    // docs/BUILD_SPEC.md §5 names this table "search_history" (singular);
    // Eloquent's default pluralization would look for "search_histories".
    protected $table = 'search_history';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'query',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
