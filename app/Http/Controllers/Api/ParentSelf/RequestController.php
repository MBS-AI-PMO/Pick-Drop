<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\PickupRequest;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class RequestController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $q = PickupRequest::with(['student', 'city', 'area', 'driver', 'vehicle'])
                ->where('parent_id', $request->user()->id)
                ->orderByDesc('id');

            if ($request->filled('status')) {
                $q->where('status', $request->string('status'));
            }

            $requests = $q->paginate(20);

            return $this->successResponse($requests, 'Requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch requests');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'type'       => ['required', 'in:parent,self'],
                'student_id' => ['nullable', 'integer', 'exists:students,id'],
                'city_id'    => ['required', 'integer', 'exists:cities,id'],
                'area_id'    => ['required', 'integer', 'exists:areas,id'],
                'pickup_point' => ['required', 'string', 'max:255'],
                'pickup_lat'   => ['required', 'numeric', 'between:-90,90'],
                'pickup_lng'   => ['required', 'numeric', 'between:-180,180'],
                'drop_point'   => ['required', 'string', 'max:255'],
                'drop_lat'     => ['required', 'numeric', 'between:-90,90'],
                'drop_lng'     => ['required', 'numeric', 'between:-180,180'],
                'pickup_time'  => ['required', 'date_format:H:i'],
                'drop_time'    => ['required', 'date_format:H:i'],
                'days'         => ['required', 'array', 'min:1'],
                'days.*'       => ['string'],
                'scheduled_date' => ['nullable', 'date'],
            ]);

            if (!empty($validated['student_id'])) {
                $student = Student::where('id', $validated['student_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();
                if (!$student) {
                    return $this->errorResponse('Invalid student_id', 422);
                }
            }

            $req = PickupRequest::create([
                'type' => $validated['type'],
                'parent_id' => $request->user()->id,
                'student_id' => $validated['student_id'] ?? null,
                'city_id' => $validated['city_id'],
                'area_id' => $validated['area_id'],
                'pickup_point' => $validated['pickup_point'],
                'pickup_lat' => $validated['pickup_lat'],
                'pickup_lng' => $validated['pickup_lng'],
                'drop_point' => $validated['drop_point'],
                'drop_lat' => $validated['drop_lat'],
                'drop_lng' => $validated['drop_lng'],
                'pickup_time' => $validated['pickup_time'],
                'drop_time' => $validated['drop_time'],
                'days' => $validated['days'],
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'status' => 'pending',
            ]);

            return $this->successResponse($req, 'Request created', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to create request');
        }
    }

    public function show(Request $request, int $requestId): JsonResponse
    {
        try {
            $req = PickupRequest::with(['student', 'city', 'area', 'driver', 'vehicle'])
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$req) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse($req, 'Request detail');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch request');
        }
    }

    public function update(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }
            if (in_array($pickupRequest->status, ['accepted', 'picked_up', 'dropped', 'completed'], true)) {
                return $this->errorResponse('Request cannot be updated after acceptance', 422);
            }

            $validated = $request->validate([
                'pickup_point' => ['sometimes', 'string', 'max:255'],
                'pickup_lat'   => ['sometimes', 'numeric', 'between:-90,90'],
                'pickup_lng'   => ['sometimes', 'numeric', 'between:-180,180'],
                'drop_point'   => ['sometimes', 'string', 'max:255'],
                'drop_lat'     => ['sometimes', 'numeric', 'between:-90,90'],
                'drop_lng'     => ['sometimes', 'numeric', 'between:-180,180'],
                'pickup_time'  => ['sometimes', 'date_format:H:i'],
                'drop_time'    => ['sometimes', 'date_format:H:i'],
                'days'         => ['sometimes', 'array', 'min:1'],
                'days.*'       => ['string'],
                'city_id'    => ['sometimes', 'integer', 'exists:cities,id'],
                'area_id'    => ['sometimes', 'integer', 'exists:areas,id'],
                'student_id' => ['sometimes', 'nullable', 'integer', 'exists:students,id'],
                'scheduled_date' => ['sometimes', 'nullable', 'date'],
            ]);

            if (array_key_exists('student_id', $validated) && !empty($validated['student_id'])) {
                $student = Student::where('id', $validated['student_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();
                if (!$student) {
                    return $this->errorResponse('Invalid student_id', 422);
                }
            }

            $pickupRequest->fill($validated);
            $pickupRequest->save();

            return $this->successResponse($pickupRequest, 'Request updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update request');
        }
    }

    public function cancel(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }
            if (in_array($pickupRequest->status, ['picked_up', 'dropped', 'completed'], true)) {
                return $this->errorResponse('Request cannot be cancelled after trip started', 422);
            }

            $pickupRequest->status = 'cancelled';
            $pickupRequest->cancelled_at = now();
            $pickupRequest->save();

            return $this->successResponse(null, 'Request cancelled');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to cancel request');
        }
    }

    public function driverInfo(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::with(['driver', 'vehicle'])
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse([
                'driver' => $pickupRequest->driver,
                'vehicle' => $pickupRequest->vehicle,
                'status' => $pickupRequest->status,
            ], 'Driver info');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch driver info');
        }
    }

    public function tracking(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            // NOTE: Real tracking will come from driver's live location table/stream.
            $tracking = [
                'status' => $pickupRequest->status,
                'driver_id' => $pickupRequest->driver_id,
                'vehicle_id' => $pickupRequest->vehicle_id,
            ];

            return $this->successResponse($tracking, 'Tracking info');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch tracking info');
        }
    }
}

