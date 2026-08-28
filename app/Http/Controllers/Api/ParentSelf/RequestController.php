<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Http\Controllers\Api\ParentSelf\BaseApiController;
use App\Models\PickupRequest;
use App\Models\Student;
use App\Models\User;
use App\Services\AppNotificationService;
use App\Services\InvoiceService;
use App\Services\ShiftFareService;
use App\Services\ShiftStopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class RequestController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $q = PickupRequest::with(['student', 'city', 'area', 'dropArea', 'driver', 'vehicle', 'stops.area', 'latestInvoice.items', 'latestInvoice.payments'])
                ->where('parent_id', $request->user()->id)
                ->orderByDesc('id');

            if ($request->filled('status')) {
                $q->where('status', $request->string('status'));
            }

            $requests = $q->paginate(\App\Support\AppPagination::PER_PAGE);
            $requests->getCollection()->transform(fn (PickupRequest $row) => $row->toApiArray());

            return $this->successResponse($requests, 'Requests');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch requests');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $onboardingDenied = $this->denyUnlessOnboardingComplete($user);
            if ($onboardingDenied) {
                return $onboardingDenied;
            }

            $this->applyLocationDefaults($request, $user);

            $validated = $request->validate([
                'type'       => ['required', 'in:parent,self'],
                'student_id' => ['nullable', 'integer', 'exists:students,id'],
                'city_id'    => ['required', 'integer', 'exists:cities,id'],
                'area_id'    => ['required', 'integer', 'exists:areas,id'],
                'drop_area_id' => ['nullable', 'integer', 'exists:areas,id'],
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
                'duration_months' => ['nullable', 'integer', 'min:1', 'max:24'],
                'passenger_count' => ['nullable', 'integer', 'min:1', 'max:20'],
                'shift_start_date' => ['nullable', 'date'],
                'scheduled_date' => ['nullable', 'date'],
                'stops' => ['nullable', 'array', 'min:2'],
                'stops.*.type' => ['required_with:stops', 'in:pickup,drop'],
                'stops.*.point' => ['required_with:stops', 'string', 'max:255'],
                'stops.*.name' => ['nullable', 'string', 'max:255'],
                'stops.*.lat' => ['required_with:stops', 'numeric', 'between:-90,90'],
                'stops.*.lng' => ['required_with:stops', 'numeric', 'between:-180,180'],
                'stops.*.time' => ['required_with:stops', 'date_format:H:i'],
                'stops.*.area_id' => ['nullable', 'integer', 'exists:areas,id'],
                'stops.*.notes' => ['nullable', 'string', 'max:500'],
            ]);

            if (empty($validated['duration_months'])) {
                $validated['duration_months'] = 1;
            }

            $validated['shift_start_date'] = $validated['shift_start_date']
                ?? $validated['scheduled_date']
                ?? now()->toDateString();

            if ($validated['type'] !== $this->expectedAccountType($request)) {
                return $this->errorResponse('Request type must match this account.', 422);
            }

            if ($user->isParentAccount() && empty($validated['student_id'])) {
                return $this->errorResponse('student_id is required for parent requests.', 422);
            }

            if (!empty($validated['student_id'])) {
                $student = Student::where('id', $validated['student_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();
                if (!$student) {
                    return $this->errorResponse('Invalid student_id', 422);
                }
            }

            $this->assertAreaBelongsToCity((int) $validated['city_id'], (int) $validated['area_id'], 'area_id');
            if (!empty($validated['drop_area_id'])) {
                $this->assertAreaBelongsToCity((int) $validated['city_id'], (int) $validated['drop_area_id'], 'drop_area_id');
            }

            $stopService = app(ShiftStopService::class);
            $stopPayload = $validated['stops'] ?? [
                [
                    'type' => 'pickup',
                    'name' => 'Pickup',
                    'point' => $validated['pickup_point'],
                    'lat' => $validated['pickup_lat'],
                    'lng' => $validated['pickup_lng'],
                    'time' => $validated['pickup_time'],
                    'area_id' => $validated['area_id'],
                ],
                [
                    'type' => 'drop',
                    'name' => 'Drop',
                    'point' => $validated['drop_point'],
                    'lat' => $validated['drop_lat'],
                    'lng' => $validated['drop_lng'],
                    'time' => $validated['drop_time'],
                    'area_id' => $validated['drop_area_id'] ?? null,
                ],
            ];

            $quote = app(ShiftFareService::class)->quote(
                (float) $validated['pickup_lat'],
                (float) $validated['pickup_lng'],
                (float) $validated['drop_lat'],
                (float) $validated['drop_lng'],
                $validated['days'],
                (int) $validated['duration_months'],
                $validated['shift_start_date'],
                $stopPayload
            );

            $req = PickupRequest::create([
                'type' => $validated['type'],
                'parent_id' => $request->user()->id,
                'student_id' => $validated['student_id'] ?? null,
                'passenger_count' => $validated['passenger_count'] ?? 1,
                'city_id' => $validated['city_id'],
                'area_id' => $validated['area_id'],
                'drop_area_id' => $validated['drop_area_id'] ?? null,
                'pickup_point' => $validated['pickup_point'],
                'pickup_lat' => $validated['pickup_lat'],
                'pickup_lng' => $validated['pickup_lng'],
                'drop_point' => $validated['drop_point'],
                'drop_lat' => $validated['drop_lat'],
                'drop_lng' => $validated['drop_lng'],
                'pickup_time' => $validated['pickup_time'],
                'drop_time' => $validated['drop_time'],
                'days' => $validated['days'],
                'duration_months' => $quote['duration_months'],
                'shift_start_date' => $quote['shift_start_date'],
                'shift_end_date' => $quote['shift_end_date'],
                'distance_km' => $quote['distance_km'],
                'trip_count' => $quote['trip_count'],
                'estimated_amount' => $quote['estimated_amount'],
                'driver_monthly_rate' => $quote['driver_monthly_rate'],
                'driver_payout_amount' => $quote['driver_payout_amount'],
                'driver_payout_status' => PickupRequest::DRIVER_PAYOUT_UNPAID,
                'driver_payout_due_on' => $quote['driver_payout_due_on'],
                'payment_status' => PickupRequest::PAYMENT_UNPAID,
                'scheduled_date' => $validated['scheduled_date'] ?? $quote['shift_start_date'],
                'status' => 'pending',
                'match_expires_at' => now()->addMinutes(\App\Services\PickupRequestAssignmentService::MATCH_TIMEOUT_MINUTES),
            ]);

            $stopService->sync($req, $stopPayload);
            $req->load(['student', 'city', 'area', 'dropArea', 'stops.area']);

            $notifier = app(AppNotificationService::class);
            $notifier->notifyParentRequestSubmitted($req);
            $notifier->notifyDriversOfNewPickupRequest($req);

            return $this->successResponse($req->toApiArray(), 'Request created. Drivers in this city and area can now see it.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to create request');
        }
    }

    public function show(Request $request, int $requestId): JsonResponse
    {
        try {
            $req = PickupRequest::with(['student', 'city', 'area', 'dropArea', 'driver', 'vehicle', 'stops.area', 'latestInvoice.items', 'latestInvoice.payments'])
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$req) {
                return $this->errorResponse('Not found', 404);
            }

            return $this->successResponse($req->toApiArray(), 'Request detail');
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
                'drop_area_id' => ['sometimes', 'nullable', 'integer', 'exists:areas,id'],
                'student_id' => ['sometimes', 'nullable', 'integer', 'exists:students,id'],
                'scheduled_date' => ['sometimes', 'nullable', 'date'],
                'duration_months' => ['sometimes', 'integer', 'min:1', 'max:24'],
                'shift_start_date' => ['sometimes', 'nullable', 'date'],
                'stops' => ['sometimes', 'array', 'min:2'],
                'stops.*.type' => ['required_with:stops', 'in:pickup,drop'],
                'stops.*.point' => ['required_with:stops', 'string', 'max:255'],
                'stops.*.name' => ['nullable', 'string', 'max:255'],
                'stops.*.lat' => ['required_with:stops', 'numeric', 'between:-90,90'],
                'stops.*.lng' => ['required_with:stops', 'numeric', 'between:-180,180'],
                'stops.*.time' => ['required_with:stops', 'date_format:H:i'],
                'stops.*.area_id' => ['nullable', 'integer', 'exists:areas,id'],
                'stops.*.notes' => ['nullable', 'string', 'max:500'],
            ]);

            if (array_key_exists('student_id', $validated) && !empty($validated['student_id'])) {
                $student = Student::where('id', $validated['student_id'])
                    ->where('parent_id', $request->user()->id)
                    ->first();
                if (!$student) {
                    return $this->errorResponse('Invalid student_id', 422);
                }
            }

            $locationChanged = $pickupRequest->status === 'pending' && (
                (array_key_exists('city_id', $validated) && (int) $validated['city_id'] !== (int) $pickupRequest->city_id)
                || (array_key_exists('area_id', $validated) && (int) $validated['area_id'] !== (int) $pickupRequest->area_id)
                || (array_key_exists('drop_area_id', $validated) && (int) ($validated['drop_area_id'] ?? 0) !== (int) ($pickupRequest->drop_area_id ?? 0))
            );

            $pickupRequest->fill($validated);

            $this->assertAreaBelongsToCity(
                (int) $pickupRequest->city_id,
                (int) $pickupRequest->area_id,
                'area_id'
            );
            if ($pickupRequest->drop_area_id) {
                $this->assertAreaBelongsToCity(
                    (int) $pickupRequest->city_id,
                    (int) $pickupRequest->drop_area_id,
                    'drop_area_id'
                );
            }

            $pickupRequest->save();
            if (array_key_exists('stops', $validated)) {
                app(ShiftStopService::class)->sync($pickupRequest, $validated['stops']);
            } else {
                app(ShiftStopService::class)->ensureDefaults($pickupRequest);
            }
            app(ShiftFareService::class)->apply($pickupRequest)->save();
            $pickupRequest->load(['student', 'city', 'area', 'dropArea', 'driver', 'vehicle', 'stops.area']);

            $notifier = app(AppNotificationService::class);
            $notifier->notifyParentRequestUpdated($pickupRequest);
            if ($locationChanged) {
                $notifier->notifyDriversOfNewPickupRequest($pickupRequest);
            }

            return $this->successResponse($pickupRequest->toApiArray(), 'Request updated');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
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
            if (in_array($pickupRequest->status, ['cancelled'], true)) {
                return $this->errorResponse('Request cannot be cancelled after it was cancelled', 422);
            }

            $run = \App\Models\ShiftDayRun::query()
                ->where('pickup_request_id', $pickupRequest->id)
                ->whereDate('date', now()->toDateString())
                ->first();

            if ($run && in_array($run->status, ['picked_up', 'dropped', 'completed'], true)) {
                return $this->errorResponse('Request cannot be cancelled after today\'s trip started', 422);
            }

            $cancelled = app(\App\Services\CancellationService::class)->cancel($pickupRequest, $request->user());

            return $this->successResponse([
                'request' => $cancelled->toApiArray(),
                'cancellation' => app(\App\Services\CancellationService::class)->preview($cancelled),
            ], 'Request cancelled');
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

            $tracking = app(\App\Services\TrackingService::class)->payload($pickupRequest);

            return $this->successResponse($tracking, 'Tracking info');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch tracking info');
        }
    }

    public function renew(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::query()
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            if (in_array($pickupRequest->status, ['cancelled', 'pending'], true) || !$pickupRequest->isShiftPaid()) {
                return $this->errorResponse('This shift cannot be renewed yet.', 422);
            }

            $validated = $request->validate([
                'duration_months' => ['nullable', 'integer', 'min:1', 'max:12'],
                'auto_renew' => ['nullable', 'boolean'],
            ]);

            if (array_key_exists('auto_renew', $validated)) {
                $pickupRequest->update(['auto_renew' => (bool) $validated['auto_renew']]);
            }

            $invoice = app(InvoiceService::class)->createRenewalInvoice(
                $pickupRequest,
                (int) ($validated['duration_months'] ?? 1)
            );

            return $this->successResponse([
                'request' => $pickupRequest->fresh()->toApiArray(),
                'invoice' => $invoice->toApiArray(),
            ], 'Renewal invoice created. Pay it before the current shift ends.');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to renew shift');
        }
    }

    public function autoRenew(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::query()
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            $validated = $request->validate([
                'enabled' => ['required', 'boolean'],
            ]);

            $pickupRequest->update(['auto_renew' => (bool) $validated['enabled']]);

            return $this->successResponse($pickupRequest->fresh()->toApiArray(), $validated['enabled']
                ? 'Auto-renew enabled'
                : 'Auto-renew disabled');
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to update auto-renew');
        }
    }

    public function declineRenewal(Request $request, int $requestId): JsonResponse
    {
        try {
            $pickupRequest = PickupRequest::query()
                ->where('id', $requestId)
                ->where('parent_id', $request->user()->id)
                ->first();

            if (!$pickupRequest) {
                return $this->errorResponse('Not found', 404);
            }

            $pickupRequest->update([
                'auto_renew' => false,
                'renewal_status' => 'declined',
            ]);

            return $this->successResponse($pickupRequest->fresh()->toApiArray(), 'Renewal declined. Service will stop on the current end date.');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to decline renewal');
        }
    }

    private function applyLocationDefaults(Request $request, User $user): void
    {
        if (!$request->filled('type')) {
            $request->merge(['type' => $this->expectedAccountType($request)]);
        }

        $defaults = [];

        if ($user->isSelfAccount() && $user->commuteProfile) {
            $profile = $user->commuteProfile;
            $defaults = [
                'city_id' => $profile->city_id,
                'area_id' => $profile->pickup_area_id,
                'drop_area_id' => $profile->drop_area_id,
                'pickup_point' => $profile->pickup_point,
                'pickup_lat' => $profile->pickup_lat,
                'pickup_lng' => $profile->pickup_lng,
                'drop_point' => $profile->drop_point,
                'drop_lat' => $profile->drop_lat,
                'drop_lng' => $profile->drop_lng,
                'pickup_time' => $this->formatHm($profile->pickup_time),
                'drop_time' => $this->formatHm($profile->drop_time),
                'days' => $profile->days,
            ];
        }

        if ($user->isParentAccount() && $request->filled('student_id')) {
            $student = Student::query()
                ->where('id', $request->integer('student_id'))
                ->where('parent_id', $user->id)
                ->first();

            if ($student) {
                $defaults = [
                    'city_id' => $student->city_id,
                    'area_id' => $student->pickup_area_id,
                    'pickup_point' => $student->pickup_location,
                    'pickup_lat' => $student->pickup_lat,
                    'pickup_lng' => $student->pickup_lng,
                    'drop_point' => $student->school_location ?: $student->school_name,
                    'pickup_time' => $this->formatHm($student->pickup_time),
                    'drop_time' => $this->formatHm($student->dropoff_time),
                ];
            }
        }

        foreach ($defaults as $key => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if (!$request->filled($key)) {
                $request->merge([$key => $value]);
            }
        }
    }

    private function formatHm(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}

