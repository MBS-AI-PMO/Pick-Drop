<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Api\Driver\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            $raw = $request->input('service_areas', $request->input('area_ids'));
            if (!is_array($raw)) {
                $raw = [];
            }

            $validated = Validator::make(
                ['area_ids' => $raw],
                [
                    'area_ids'   => ['required', 'array', 'min:1'],
                    'area_ids.*' => ['integer', 'exists:areas,id'],
                ]
            )->validate();

            $user = $request->user();
            $ids = array_values(array_unique(array_map('intval', $validated['area_ids'])));

            if ($user->city_id) {
                $this->assertAreaIdsBelongToCity((int) $user->city_id, $ids);
            }

            $user->service_areas = $ids;
            $user->save();

            return $this->successResponse($ids, 'Service areas updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update service areas');
        }
    }
}

