<?php

namespace App\Http\Resources\Api\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetalRateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $perGram = $this->rate_per_gram_paise / 100;

        return [
            'purity' => $this->purity,
            'per_gram' => round($perGram, 2),
            'per_8g' => round($perGram * 8, 2),
            'per_10g' => round($perGram * 10, 2),
            'change' => round($this->change_paise / 100, 2),
            'change_pct' => (float) $this->change_pct,
        ];
    }
}
