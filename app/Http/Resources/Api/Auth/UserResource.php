<?php

namespace App\Http\Resources\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'is_mobile_verified' => (bool) $this->is_mobile_verified,
            'mobile_verified_at' => $this->mobile_verified_at?->toIso8601String(),
        ];
    }
}
