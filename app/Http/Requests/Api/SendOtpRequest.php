<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseFormRequest;

class SendOtpRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'digits:10'],
        ];
    }
}
