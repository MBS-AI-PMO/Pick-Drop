<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
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
        // In production you might want to hide $e->getMessage() and just log it.
        report($e);

        return $this->errorResponse(
            app()->environment('local') ? $e->getMessage() : $defaultMessage,
            500
        );
    }

    /**
     * @param  list<int|string>  $areaIds
     */
    protected function assertAreaIdsBelongToCity(int $cityId, array $areaIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $areaIds)));
        if ($ids === []) {
            return;
        }

        if (Area::whereIn('id', $ids)->where('city_id', '!=', $cityId)->exists()) {
            throw ValidationException::withMessages([
                'service_areas' => ['All service area IDs must belong to the selected city.'],
            ]);
        }
    }
}

