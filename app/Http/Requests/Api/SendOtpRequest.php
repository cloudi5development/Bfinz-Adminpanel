<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class SendOtpRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'regex:/^[6-9]\d{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.regex' => 'Enter a valid 10-digit Indian mobile number.',
        ];
    }
}
