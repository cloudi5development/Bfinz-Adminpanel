<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class VerifyOtpRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'digits:10'],
            'otp' => ['required', 'digits:4'],
            'name' => ['nullable', 'string', 'max:100'],
            'fcm_token' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
            'platform' => ['nullable', 'in:android,ios,web'],
        ];
    }
}
