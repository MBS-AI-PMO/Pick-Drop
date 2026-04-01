<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Throwable;

abstract class BaseApiController extends Controller
{
    protected function successResponse(mixed $data = null, string $message = 'OK', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Something went wrong', int $code = 500, mixed $errors = null): JsonResponse
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $code);
    }

    protected function handleException(Throwable $e, string $defaultMessage = 'Server error'): JsonResponse
    {
        report($e);

        return $this->errorResponse(
            app()->environment('local') ? $e->getMessage() : $defaultMessage,
            500
        );
    }
}

