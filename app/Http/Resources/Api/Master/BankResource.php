<?php

namespace App\Http\Resources\Api\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'category' => $this->category,
            'logo_url' => $this->logo_url,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'website' => $this->website,
        ];
    }
}
