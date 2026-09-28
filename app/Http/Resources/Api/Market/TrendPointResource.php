<?php

namespace App\Http\Resources\Api\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property-read Carbon $date
 * @property-read float $rate
 */
class TrendPointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->date->toDateString(),
            'rate' => (float) $this->rate,
        ];
    }
}
