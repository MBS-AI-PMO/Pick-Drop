<?php

namespace App\Http\Controllers\Api\Driver;

use App\Models\Area;
use App\Models\DriverVerification;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class VerificationController extends BaseApiController
{
    /**
     * Step 2: Submit / resubmit KYC (personal info + city + service areas).
     * No vehicle info here.
     * Multipart form-data with image files.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->role !== 'driver') {
                return $this->errorResponse('Only drivers can submit verification.', 403);
            }

            if (is_null($user->email_verified_at)) {
                return $this->errorResponse('Please verify your email before submitting KYC.', 403);
            }

            $existing = $user->driverVerification;

            if ($existing && $existing->isPending()) {
                return $this->errorResponse('Your verification is already pending admin review.', 422);
            }

            if ($existing && $existing->isApproved()) {
                return $this->errorResponse('Your verification is already approved.', 422);
            }

            // Normalize service_areas / area_ids from multipart form
            $rawAreas = $request->input('service_areas', $request->input('area_ids'));
            if (is_string($rawAreas)) {
                $decoded = json_decode($rawAreas, true);
                $rawAreas = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $rawAreas)));
            }
            if (!is_array($rawAreas)) {
                $rawAreas = [];
            }
            $request->merge(['service_areas' => array_values($rawAreas)]);

            $validated = $request->validate([
                // name / phone / email registration se auto aate hain — yahan change nahi
                'father_name' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'address' => ['required', 'string', 'max:500'],
                'city_id' => ['required', 'integer', 'exists:cities,id'],

                // Service areas selected with personal info (same city)
                'service_areas' => ['required', 'array', 'min:1'],
                'service_areas.*' => ['integer', 'exists:areas,id'],

                'cnic_number' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/',
                    'unique:driver_verifications,cnic_number' . ($existing ? ',' . $existing->id : ''),
                ],
                'cnic_front' => ['required', 'image', 'max:5120'],
                'cnic_back' => ['required', 'image', 'max:5120'],
                'selfie_photo' => ['required', 'image', 'max:5120'],

                'license_number' => ['required', 'string', 'max:50'],
                'license_front' => ['required', 'image', 'max:5120'],
                'license_back' => ['required', 'image', 'max:5120'],
                'license_expiry' => ['required', 'date', 'after:today'],

                'terms_accepted' => ['required', 'accepted'],
            ]);

            $fullName = $user->name;

            $cityId = (int) $validated['city_id'];
            $serviceAreaIds = array_values(array_unique(array_map('intval', $validated['service_areas'])));
            $this->assertAreaIdsBelongToCity($cityId, $serviceAreaIds);

            $dir = 'driver-kyc/' . $user->id;
            $paths = [];

            DB::beginTransaction();

            try {
                $paths['cnic_front'] = $this->storeDocument($request->file('cnic_front'), $dir, 'cnic_front');
                $paths['cnic_back'] = $this->storeDocument($request->file('cnic_back'), $dir, 'cnic_back');
                $paths['selfie_photo'] = $this->storeDocument($request->file('selfie_photo'), $dir, 'selfie');
                $paths['license_front'] = $this->storeDocument($request->file('license_front'), $dir, 'license_front');
                $paths['license_back'] = $this->storeDocument($request->file('license_back'), $dir, 'license_back');

                $payload = [
                    'full_name' => $fullName,
                    'father_name' => $validated['father_name'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'],
                    'address' => $validated['address'],
                    'city_id' => $cityId,
                    'cnic_number' => $this->normalizeCnic($validated['cnic_number']),
                    'cnic_front' => $paths['cnic_front'],
                    'cnic_back' => $paths['cnic_back'],
                    'selfie_photo' => $paths['selfie_photo'],
                    'license_number' => $validated['license_number'],
                    'license_front' => $paths['license_front'],
                    'license_back' => $paths['license_back'],
                    'license_expiry' => $validated['license_expiry'],
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                    'status' => DriverVerification::STATUS_PENDING,
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ];

                if ($existing) {
                    $this->deleteOldDocuments($existing, array_keys($paths));
                    $existing->update($payload);
                    $verification = $existing->fresh(['city']);
                } else {
                    $verification = DriverVerification::create([
                        'user_id' => $user->id,
                        ...$payload,
                    ])->load('city');
                }

                // City + service areas only — name/phone/email registration se locked
                $user->update([
                    'city_id' => $cityId,
                    'service_areas' => $serviceAreaIds,
                    'status' => 'Pending',
                ]);

                Notification::create([
                    'title' => 'Driver KYC Pending',
                    'message' => $user->name . ' submitted driver verification for review.',
                    'type' => 'info',
                ]);

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                $this->cleanupPaths($paths);
                throw $e;
            }

            $selectedAreas = Area::whereIn('id', $serviceAreaIds)->orderBy('name')->get()->values()->all();

            return $this->successResponse([
                'kyc_status' => $verification->status,
                'vehicle_verification_status' => $user->fresh()->vehicleVerificationStatus(),
                'prefill' => $this->accountPrefill($user->fresh()),
                'city_id' => $cityId,
                'service_areas' => $selectedAreas,
                'next_step' => 'kyc_pending',
                'verification' => $verification->toApiArray(),
            ], 'Verification submitted successfully. Status: Pending Verification.', 201);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to submit verification');
        }
    }

    public function show(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $verification = $user->driverVerification;

            return $this->successResponse([
                'kyc_status' => $user->kycStatus(),
                'vehicle_verification_status' => $user->vehicleVerificationStatus(),
                // App KYC form: name/phone/email auto-fill (readonly from registration)
                'prefill' => $this->accountPrefill($user),
                'city_id' => $user->city_id,
                'service_areas' => $user->toDriverApiArray()['service_areas'] ?? [],
                'next_step' => $user->driverNextStep(),
                'verification' => $verification?->toApiArray(),
            ], 'Driver verification status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load verification');
        }
    }

    /**
     * Registration details — KYC form mein auto-fill / locked.
     *
     * @return array{full_name: string, phone: ?string, email: string, readonly_fields: list<string>}
     */
    private function accountPrefill($user): array
    {
        return [
            'full_name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'readonly_fields' => ['full_name', 'phone', 'email'],
        ];
    }

    private function storeDocument(UploadedFile $file, string $dir, string $prefix): string
    {
        $name = $prefix . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($dir, $name, 'public');
    }

    private function normalizeCnic(string $cnic): string
    {
        $digits = preg_replace('/\D+/', '', $cnic) ?? '';

        if (strlen($digits) === 13) {
            return substr($digits, 0, 5) . '-' . substr($digits, 5, 7) . '-' . substr($digits, 12, 1);
        }

        return $cnic;
    }

    /**
     * @param  list<string>  $fields
     */
    private function deleteOldDocuments(DriverVerification $verification, array $fields): void
    {
        foreach ($fields as $field) {
            $path = $verification->{$field} ?? null;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * @param  array<string, string>  $paths
     */
    private function cleanupPaths(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
