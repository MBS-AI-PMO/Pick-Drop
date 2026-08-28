<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\City;
use App\Models\Notification;
use App\Models\ParentSelfVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class VerificationController extends BaseApiController
{
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $emailDenied = $this->denyUnlessEmailVerified($user);
            if ($emailDenied) {
                return $emailDenied;
            }

            $existing = $user->parentSelfVerification;

            if ($existing && $existing->isPending()) {
                return $this->errorResponse('Your verification is already pending admin review.', 422);
            }

            if ($existing && $existing->isApproved()) {
                return $this->errorResponse('Your verification is already approved.', 422);
            }

            $validated = $request->validate([
                'father_name' => ['nullable', 'string', 'max:255'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'gender' => ['nullable', 'string', 'in:male,female,other'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'address' => ['required', 'string', 'max:500'],
                'country' => ['nullable', 'string', 'max:100'],
                'city_id' => ['required', 'integer', 'exists:cities,id'],
                'complete_address' => ['nullable', 'string', 'max:1000'],
                'postal_code' => ['nullable', 'string', 'max:30'],
                'cnic_number' => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/',
                    'unique:parent_self_verifications,cnic_number' . ($existing ? ',' . $existing->id : ''),
                ],
                'cnic_front' => ['required', 'image', 'max:5120'],
                'cnic_back' => ['required', 'image', 'max:5120'],
                'selfie_photo' => ['required', 'image', 'max:5120'],
                'terms_accepted' => ['required', 'accepted'],
            ]);

            $cityId = (int) $validated['city_id'];
            $this->assertCityIsActive($cityId);
            $city = City::find($cityId);

            $dir = 'parent-self-kyc/' . $user->id;
            $paths = [];

            DB::beginTransaction();

            try {
                $paths['cnic_front'] = $this->storeDocument($request->file('cnic_front'), $dir, 'cnic_front');
                $paths['cnic_back'] = $this->storeDocument($request->file('cnic_back'), $dir, 'cnic_back');
                $paths['selfie_photo'] = $this->storeDocument($request->file('selfie_photo'), $dir, 'selfie');

                $payload = [
                    'account_type' => $this->expectedAccountType($request),
                    'full_name' => $user->name,
                    'father_name' => $validated['father_name'] ?? null,
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'] ?? null,
                    'nationality' => $validated['nationality'] ?? 'Pakistani',
                    'address' => $validated['address'],
                    'country' => $validated['country'] ?? 'Pakistan',
                    'city_id' => $cityId,
                    'city_name' => $city?->name,
                    'complete_address' => $validated['complete_address'] ?? $validated['address'],
                    'postal_code' => $validated['postal_code'] ?? null,
                    'cnic_number' => $this->normalizeCnic($validated['cnic_number']),
                    'cnic_front' => $paths['cnic_front'],
                    'cnic_back' => $paths['cnic_back'],
                    'selfie_photo' => $paths['selfie_photo'],
                    'terms_accepted' => true,
                    'terms_accepted_at' => now(),
                    'status' => ParentSelfVerification::STATUS_PENDING,
                    'rejection_reason' => null,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                ];

                if ($existing) {
                    $this->deleteOldDocuments($existing, array_keys($paths));
                    $existing->update($payload);
                    $verification = $existing->fresh(['city']);
                } else {
                    $verification = ParentSelfVerification::create([
                        'user_id' => $user->id,
                        ...$payload,
                    ])->load('city');
                }

                $details = is_array($user->details) ? $user->details : [];
                $details['address'] = $validated['address'];

                $user->update([
                    'city_id' => $cityId,
                    'status' => 'Pending',
                    'details' => $details,
                ]);

                $label = $user->isSelfAccount() ? 'Self' : 'Parent';
                Notification::create([
                    'title' => $label . ' KYC Pending',
                    'message' => $user->name . ' submitted identity verification for review.',
                    'type' => 'info',
                ]);

                DB::commit();
            } catch (Throwable $e) {
                DB::rollBack();
                $this->cleanupPaths($paths);
                throw $e;
            }

            $user = $user->fresh();

            return $this->successResponse([
                'kyc_status' => $verification->status,
                'account_type' => $user->role,
                'prefill' => $this->accountPrefill($user),
                'next_step' => $user->parentSelfNextStep(),
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
            $accountDenied = $this->denyUnlessAccountType($user, $request);
            if ($accountDenied) {
                return $accountDenied;
            }

            $verification = $user->parentSelfVerification;

            return $this->successResponse([
                'kyc_status' => $user->parentSelfKycStatus(),
                'account_type' => strtolower((string) $user->role),
                'prefill' => $this->accountPrefill($user),
                'city_id' => $user->city_id,
                'cities' => City::dropdownWithAreas(),
                'next_step' => $user->parentSelfNextStep(),
                'onboarding_complete' => $user->isParentSelfOnboardingComplete(),
                'verification' => $verification?->toApiArray(),
            ], 'Identity verification status');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to load verification');
        }
    }

    /**
     * @return array{full_name: string, phone: ?string, email: string, address: ?string, readonly_fields: list<string>}
     */
    private function accountPrefill($user): array
    {
        $details = is_array($user->details) ? $user->details : [];

        return [
            'full_name' => $user->name,
            'phone' => $user->phone ?? ($details['contact'] ?? null),
            'email' => $user->email,
            'address' => $details['address'] ?? null,
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
    private function deleteOldDocuments(ParentSelfVerification $verification, array $fields): void
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
