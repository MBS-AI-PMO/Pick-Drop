<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesVerificationDocuments;
use App\Models\City;
use App\Models\DriverVerification;
use App\Models\DriverVehicleVerification;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Services\AppNotificationService;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DriverVerificationController extends Controller
{
    use ManagesVerificationDocuments;

    public function index(Request $request)
    {
        try {
            $query = DriverVerification::with(['user', 'city', 'reviewer'])
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('cnic_number', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            $verifications = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

            return view('pickdrop.driver-verifications.index', compact('verifications'));
        } catch (\Throwable $e) {
            Log::error('Failed to load driver verifications', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load verifications: ' . $e->getMessage());
        }
    }

    public function show(DriverVerification $driverVerification)
    {
        $driverVerification->load(['user', 'city', 'reviewer']);

        return view('pickdrop.driver-verifications.show', [
            'verification' => $driverVerification,
        ]);
    }

    public function edit(DriverVerification $driverVerification)
    {
        $driverVerification->load(['user', 'city']);
        $cities = City::active()->orderBy('name')->get(['id', 'name']);

        return view('pickdrop.driver-verifications.edit', [
            'verification' => $driverVerification,
            'cities' => $cities,
        ]);
    }

    public function update(Request $request, DriverVerification $driverVerification)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'address' => ['required', 'string', 'max:500'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'cnic_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]{5}-?[0-9]{7}-?[0-9]{1}$/',
                Rule::unique('driver_verifications', 'cnic_number')->ignore($driverVerification->id),
            ],
            'license_number' => ['required', 'string', 'max:100'],
            'license_expiry' => ['required', 'date'],
            'cnic_front' => ['nullable', 'image', 'max:5120'],
            'cnic_back' => ['nullable', 'image', 'max:5120'],
            'selfie_photo' => ['nullable', 'image', 'max:5120'],
            'license_front' => ['nullable', 'image', 'max:5120'],
            'license_back' => ['nullable', 'image', 'max:5120'],
        ]);

        try {
            $payload = [
                'full_name' => $validated['full_name'],
                'father_name' => $validated['father_name'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'address' => $validated['address'],
                'city_id' => (int) $validated['city_id'],
                'cnic_number' => $this->normalizeCnicNumber($validated['cnic_number']),
                'license_number' => $validated['license_number'],
                'license_expiry' => $validated['license_expiry'],
            ];

            $documentUpdates = $this->replaceVerificationDocuments(
                $driverVerification,
                $request->allFiles(),
                'driver-kyc/' . $driverVerification->user_id,
                [
                    'cnic_front' => 'cnic_front',
                    'cnic_back' => 'cnic_back',
                    'selfie_photo' => 'selfie',
                    'license_front' => 'license_front',
                    'license_back' => 'license_back',
                ]
            );

            $driverVerification->update(array_merge($payload, $documentUpdates));

            $driverVerification->user?->update([
                'city_id' => (int) $validated['city_id'],
            ]);

            return redirect()
                ->route('driver-verifications.show', $driverVerification)
                ->with('success', 'Driver verification updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update driver verification details', [
                'id' => $driverVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update verification: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, DriverVerification $driverVerification)
    {
        $request->merge(['status' => DriverVerification::STATUS_APPROVED]);

        return $this->updateStatus($request, $driverVerification);
    }

    public function reject(Request $request, DriverVerification $driverVerification)
    {
        $request->merge(['status' => DriverVerification::STATUS_REJECTED]);

        return $this->updateStatus($request, $driverVerification);
    }

    public function updateStatus(Request $request, DriverVerification $driverVerification)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ]);

        try {
            $status = $validated['status'];
            $previousStatus = $driverVerification->status;

            $driverVerification->update([
                'status' => $status,
                'rejection_reason' => $status === DriverVerification::STATUS_REJECTED
                    ? $validated['rejection_reason']
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $driverVerification->user?->update($this->userStatusPayload($driverVerification, $status));

            if ($status !== DriverVerification::STATUS_APPROVED) {
                Vehicle::where('driver_id', $driverVerification->user_id)->update([
                    'status' => 'Inactive',
                ]);
            } elseif (DriverVehicleVerification::where('user_id', $driverVerification->user_id)
                ->where('status', DriverVehicleVerification::STATUS_APPROVED)
                ->exists()) {
                Vehicle::where('driver_id', $driverVerification->user_id)->update([
                    'status' => 'Active',
                ]);
            }

            $this->notifyStatusChange($driverVerification, $status, $previousStatus);

            return redirect()
                ->route('driver-verifications.show', $driverVerification)
                ->with('success', 'Driver verification status updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update driver verification status', [
                'id' => $driverVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    private function userStatusPayload(DriverVerification $driverVerification, string $status): array
    {
        if ($status === DriverVerification::STATUS_APPROVED) {
            return [
                'status' => 'Pending',
                'city_id' => $driverVerification->city_id,
            ];
        }

        return [
            'status' => 'Pending',
        ];
    }

    private function notifyStatusChange(DriverVerification $driverVerification, string $status, string $previousStatus): void
    {
        if ($status === $previousStatus) {
            return;
        }

        $driverName = $driverVerification->user?->name ?? $driverVerification->full_name;
        $payload = match ($status) {
            DriverVerification::STATUS_APPROVED => [
                'title' => 'Driver KYC Approved',
                'message' => $driverName . ' KYC has been approved and is ready for vehicle verification.',
                'type' => 'success',
            ],
            DriverVerification::STATUS_REJECTED => [
                'title' => 'Driver KYC Declined',
                'message' => $driverName . ' verification was declined.',
                'type' => 'warning',
            ],
            default => [
                'title' => 'Driver KYC Pending',
                'message' => $driverName . ' verification status was moved back to pending.',
                'type' => 'info',
            ],
        };

        Notification::create($payload);

        // Send in-app notification to the driver's mobile app
        $driverMessages = match ($status) {
            DriverVerification::STATUS_APPROVED => [
                'title' => 'KYC Approved',
                'body'  => 'Your identity verification has been approved. Please complete vehicle verification to start accepting rides.',
            ],
            DriverVerification::STATUS_REJECTED => [
                'title' => 'KYC Declined',
                'body'  => 'Your identity verification was declined.' . ($driverVerification->rejection_reason ? ' Reason: ' . $driverVerification->rejection_reason : ' Please re-submit your documents.'),
            ],
            default => [
                'title' => 'KYC Status Updated',
                'body'  => 'Your identity verification status has been moved back to pending.',
            ],
        };

        if ($driverVerification->user_id) {
            app(AppNotificationService::class)->notify(
                (int) $driverVerification->user_id,
                'verification_status',
                $driverMessages['title'],
                $driverMessages['body'],
                [
                    'verification_type' => 'driver_kyc',
                    'verification_id'   => $driverVerification->id,
                    'status'            => $status,
                ]
            );
        }
    }

    public function document(DriverVerification $driverVerification, string $field)
    {
        $allowed = [
            'cnic_front',
            'cnic_back',
            'selfie_photo',
            'license_front',
            'license_back',
        ];

        if (!in_array($field, $allowed, true)) {
            abort(404);
        }

        $path = $driverVerification->{$field};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    public function destroy(DriverVerification $driverVerification)
    {
        $driverVerification->delete();

        return redirect()
            ->route('driver-verifications.index')
            ->with('success', 'Driver verification deleted.');
    }
}
