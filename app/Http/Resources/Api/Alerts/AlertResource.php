<?php

namespace App\Http\Resources\Api\Alerts;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlertResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'asset' => $this->asset,
            'city_id' => $this->city_id,
            'condition' => $this->condition,
            'target_value' => $this->target_value !== null ? (float) $this->target_value : null,
            'is_active' => (bool) $this->is_active,
            'last_triggered_at' => $this->last_triggered_at?->toIso8601String(),
        ];
    }
}
