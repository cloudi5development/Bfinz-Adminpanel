<?php

namespace App\Traits;

use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Convenience wrappers around App\Support\ApiResponse so API controllers can
 * call $this->success(...) / $this->error(...) directly.
 */
trait ApiResponser
{
    protected function success(mixed $data = null, string $message = '', int $code = 200, array $extra = []): JsonResponse
    {
        return ApiResponse::success($data, $message, $code, $extra);
    }

    protected function error(string $message = '', mixed $errors = null, int $code = 422, array $extra = []): JsonResponse
    {
        return ApiResponse::error($message, $errors, $code, $extra);
    }
}
