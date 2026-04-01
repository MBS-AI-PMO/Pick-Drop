<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequestController extends BaseApiController
{
    public function available(Request $request): JsonResponse
    {
        try {
            // TODO: Replace with real query that filters by driver service areas and seat capacity
            $requests = []; // placeholder

            return $this->successResponse($requests, 'Available requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch available requests');
        }
    }

    public function accept(Request $request, int $requestId): JsonResponse
    {
        try {
            // TODO: Implement accept logic and capacity checks

            return $this->successResponse([
                'request_id' => $requestId,
            ], 'Request accepted');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to accept request');
        }
    }

    public function reject(Request $request, int $requestId): JsonResponse
    {
        try {
            // TODO: Implement reject logic

            return $this->successResponse([
                'request_id' => $requestId,
            ], 'Request rejected');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to reject request');
        }
    }
}

