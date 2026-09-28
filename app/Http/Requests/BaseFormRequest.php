<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for every API form request. Authorization is handled by route
 * middleware (auth:sanctum) rather than per-request policies here, so this
 * always authorizes; failed validation renders through the standard
 * ApiResponse envelope via the global ValidationException handler in
 * bootstrap/app.php, so no override is needed here.
 */
abstract class BaseFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
