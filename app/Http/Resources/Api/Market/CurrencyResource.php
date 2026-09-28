<?php

namespace App\Http\Resources\Api\Market;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'country' => $this->country,
            'flag' => $this->flag,
            'group' => $this->group,
        ];
    }
}
