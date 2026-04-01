<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class ServiceAreaController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // TODO: Replace with real relation like $request->user()->serviceAreas
        $areas = $request->user()->service_areas ?? [];

        return $this->successResponse($areas, 'Service areas');
    }

    public function sync(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'area_ids'   => ['required', 'array', 'min:1'],
                'area_ids.*' => ['integer'],
            ]);

            // TODO: Persist service areas in pivot or JSON column
            $user = $request->user();
            $user->service_areas = $validated['area_ids'];
            $user->save();

            return $this->successResponse($validated['area_ids'], 'Service areas updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update service areas');
        }
    }
}

