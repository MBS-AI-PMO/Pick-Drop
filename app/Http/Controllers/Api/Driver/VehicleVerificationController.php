<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\DriverVehicleVerification;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class VehicleVerificationController extends BaseApiController
{
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->role !== 'driver') {
                return $this->errorResponse('Only drivers can submit vehicle verification.', 403);
            }

            if (is_null($user->email_verified_at)) {
                return $this->errorResponse('Please verify your email before submitting vehicle verification.', 403);
            }

            if ($user->kycStatus() !== 'approved') {
                return $this->errorResponse('Please complete and get approval for driver KYC first.', 403);
            }

            $existing = $user->vehicleVerification;

            if ($existing && $existing->isPending()) {
                return $this->errorResponse('Your vehicle verification is already pending admin review.', 422);
            }

            if ($existing && $existing->isApproved()) {
                return $this->errorResponse('Your vehicle verification is already approved.', 422);
            }

            $validated = $request->validate([
                'vehicle_category_id' => ['nullable', 'integer', 'exists:vehicle_categories,id'],
                'vehicle_name' => ['required', 'string', 'max:255'],
                'vehicle_model' => ['nullable', 'string', 'max:255'],
                'vehicle_color' => ['nullable', 'string', 'max:100'],
                'license_plate' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('driver_vehicle_verifications', 'license_plate')->ignore($existing?->id),
                ],
                'registration_card_front' => ['required', 'image', 'max:5120'],
                'registration_card_back' => ['required', 'image', 'max:5120'],
                'vehicle_front_photo' => ['required', 'image', 'max:5120'],
                'vehicle_back_photo' => ['required', 'image', 'max:5120'],
                'number_plate_photo' => ['required', 'image', 'max:5120'],
                'owner_name' => ['required', 'string', 'max:255'],
                'owner_cnic_number' => ['nullable', 'string', 'max:20'],
                'owner_document_front' => ['required', 'image', 'max:5120'],
                'owner_document_back' => ['required', 'image', 'max:5120'],
            ]);

            $dir = 'driver-vehicle-verification/' . $user->id;
            $paths = [];

            DB::beginTransaction();

            try {
                $paths['registration_card_front'] = $this->storeDocument($request->file('registration_card_front'), $dir, 'registration_front');
                $paths['registration_card_back'] = $this->storeDocument($request->file('registration_card_back'), $dir, 'registration_back');
                $paths['vehicle_front_photo'] = $this->storeDocument($request->file('vehicle_front_photo'), $dir, 'vehicle_front');
                $paths['vehicle_back_photo'] = $this->storeDocument($request->file('vehicle_back_photo'), $dir, 'vehicle_back');
                $paths['number_plate_photo'] = $this->storeDocument($request->file('number_plate_photo'), $dir, 'number_plate');
                $paths['owner_document_front'] = $this->storeDocument($request->file('owner_document_front'), $dir, 'owner_doc_front');
                $paths['owner_document_back'] = $this->storeDocument($request->file('owner_document_back'), $dir, 'owner_doc_back');

                $payload = [
                    'vehicle_category_id' => $validated['vehicle_category_id'] ?? null,
                    'vehicle_name' => $validated['vehicle_name'],
                    'vehicle_model' => $validated['vehicle_model'] ?? null,
                    'vehicle_color' => $validated['vehicle_color'] ?? null,
                    'license_plate' => strtoupper($validated['license_plate']),
                    'registration_card_front' => $paths['registration_card_front'],
                    'registration_card_back' => $paths['registration_card_back'],
                    'vehicle_front_photo' => $paths['vehicle_front_photo'],
                    'vehicle_back_photo' => $paths['vehicle_back_photo'],
                    'number_plate_photo' => $paths['number_plate_photo'],
                    'owner_name' => $validated['owner_name'],
                    'owner_cnic_number' => $validated['owner_cnic_number'] ?? null,
                    'owner_document_front' => $paths['owner_document_front'],
                    'owner_document_back' => $paths['owner_document_back'],
                    'status' => DriverVehicleVerification::STATUS_PENDING,
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ];

                if ($existing) {
                    $this->deleteOldDocuments($existing, array_keys($paths));
                    $existing->update($payload);
                    $verification = $existing->fresh(['category']);
                } else {
                    $verification = DriverVehicleVerification::create([
                        'user_id' => $user->id,
                        ...$payload,
                    ])->load('category');
                }

                $user->update([
                    'status' => 'Pending',
                ]);

                Notification::create([
                    'title' => 'Driver Vehicle Verification Pending',
                    'message' => $user->name . ' submitted vehicle verification for review.',
                    'type' => 'info',
                ]);

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                $this->cleanupPaths($paths);
                throw $e;
            }

            return $this->successResponse([
                'vehicle_verification_status' => $verification->status,
                'next_step' => 'vehicle_verification_pending',
                'vehicle_verification' => $verification->toApiArray(),
            ], 'Vehicle verification submitted successfully. Status: Pending Approval.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit vehicle verification');
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $verification = $user->vehicleVerification;

            return $this->successResponse([
                'vehicle_verification_status' => $user->vehicleVerificationStatus(),
                'service_areas_setup' => $user->hasServiceAreas(),
                'onboarding_complete' => $user->isOnboardingComplete(),
                'next_step' => $user->driverNextStep(),
                'vehicle_verification' => $verification?->toApiArray(),
            ], 'Driver vehicle verification status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load vehicle verification');
        }
    }

    private function storeDocument(UploadedFile $file, string $dir, string $prefix): string
    {
        $name = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($dir, $name, 'public');
    }

    private function deleteOldDocuments(DriverVehicleVerification $verification, array $fields): void
    {
        foreach ($fields as $field) {
            $path = $verification->{$field} ?? null;

            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    private function cleanupPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
