<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Standardized JSON envelope for every API response: { status, data, message,
 * ...extra }. A paginator passed as $data has its page info merged in at the
 * top level (current_page/per_page/total/last_page, or the cursor
 * equivalents) instead of nested under a separate key.
 */
class ApiResponse
{
    public static function success(mixed $data = null, string $message = '', int $code = 200, array $extra = []): JsonResponse
    {
        $response = ['status' => true];

        $resource = $data instanceof AnonymousResourceCollection ? $data->resource : $data;

        if ($resource instanceof CursorPaginator) {
            $response['data'] = $data instanceof AnonymousResourceCollection ? $data->collection->toArray() : $resource->items();
            $response['next_cursor'] = $resource->nextCursor()?->encode();
            $response['prev_cursor'] = $resource->previousCursor()?->encode();
            $response['has_more'] = $resource->hasMorePages();
            $response['per_page'] = $resource->perPage();
        } elseif ($resource instanceof Paginator) {
            $response['data'] = $data instanceof AnonymousResourceCollection ? $data->collection->toArray() : $resource->items();
            $response['current_page'] = $resource->currentPage();
            $response['per_page'] = $resource->perPage();
            $response['total'] = method_exists($resource, 'total') ? $resource->total() : null;
            $response['last_page'] = method_exists($resource, 'lastPage') ? $resource->lastPage() : null;
        } else {
            $response['data'] = $data;
        }

        if ($message !== '') {
            $response['message'] = $message;
        }

        foreach ($extra as $key => $value) {
            $response[$key] = $value;
        }

        return response()->json($response, $code);
    }

    public static function error(string $message = '', mixed $errors = null, int $code = 422, array $extra = []): JsonResponse
    {
        $response = ['status' => false];

        if ($message !== '') {
            $response['message'] = $message;
        }

        if (! empty($errors)) {
            $response['data'] = $errors;
        }

        foreach ($extra as $key => $value) {
            $response[$key] = $value;
        }

        return response()->json($response, $code);
    }
}
