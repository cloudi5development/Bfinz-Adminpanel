<?php

namespace App\Http\Resources\Api\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginHistoryResource extends JsonResource
{
    public function __construct(mixed $resource, private readonly int $currentTokenId = 0)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $isActive = is_null($this->logged_out_at);

        return [
            'id' => $this->id,
            'device_name' => $this->device_name,
            'platform' => $this->platform,
            'ip_address' => $this->ip_address,
            'logged_in_at' => $this->logged_in_at?->toIso8601String(),
            'logged_out_at' => $this->logged_out_at?->toIso8601String(),
            'is_active' => $isActive,
            'is_current' => $isActive && $this->token_id === $this->currentTokenId,
        ];
    }
}
