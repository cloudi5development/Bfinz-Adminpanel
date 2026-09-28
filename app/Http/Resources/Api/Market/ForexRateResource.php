<?php

namespace App\Http\Resources\Api\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForexRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->base,
            'rate' => (float) $this->rate,
            'change' => (float) $this->change,
            'change_pct' => (float) $this->change_pct,
            'updated_at' => $this->fetched_at?->toIso8601String(),
        ];
    }
}
