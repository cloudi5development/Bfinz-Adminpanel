<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class RegisterDeviceRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'in:android,ios,web'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ];
    }
}
