<?php

namespace App\Http\Resources\Api\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FuelPriceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'fuel' => $this->fuel,
            'city_id' => $this->city_id,
            'city' => $this->whenLoaded('city', fn () => $this->city->name),
            'price' => round($this->price_paise / 100, 2),
            'change' => round($this->change_paise / 100, 2),
            'updated_at' => $this->price_date?->toDateString(),
        ];
    }
}
