<?php

namespace App\Http\Controllers;

use App\Models\DriverVerification;
use App\Models\DriverVehicleVerification;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DriverVerificationController extends Controller
{
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

            $verifications = $query->paginate(10)->withQueryString();

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
}
