<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class AlertRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:gold,silver,forex,fuel,fd,rd'],
            'asset' => ['required', 'string', 'max:20'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'condition' => ['required', 'in:above,below,any_change'],
            'target_value' => ['required_unless:condition,any_change', 'nullable', 'numeric', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
