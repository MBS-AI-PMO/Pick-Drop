<?php

namespace App\Http\Controllers;

use App\Models\DriverVehicleVerification;
use App\Models\Notification;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VehicleVerificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DriverVehicleVerification::with(['user', 'category', 'reviewer'])->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('vehicle_name', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            $verifications = $query->paginate(10)->withQueryString();

            return view('pickdrop.vehicle-verifications.index', compact('verifications'));
        } catch (\Throwable $e) {
            Log::error('Failed to load vehicle verifications', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load vehicle verifications: ' . $e->getMessage());
        }
    }

    public function show(DriverVehicleVerification $vehicleVerification)
    {
        $vehicleVerification->load(['user', 'category', 'reviewer']);

        return view('pickdrop.vehicle-verifications.show', [
            'verification' => $vehicleVerification,
        ]);
    }

    public function approve(Request $request, DriverVehicleVerification $vehicleVerification)
    {
        $request->merge(['status' => DriverVehicleVerification::STATUS_APPROVED]);

        return $this->updateStatus($request, $vehicleVerification);
    }

    public function reject(Request $request, DriverVehicleVerification $vehicleVerification)
    {
        $request->merge(['status' => DriverVehicleVerification::STATUS_REJECTED]);

        return $this->updateStatus($request, $vehicleVerification);
    }

    public function updateStatus(Request $request, DriverVehicleVerification $vehicleVerification)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ]);

        try {
            $status = $validated['status'];
            $previousStatus = $vehicleVerification->status;

            $vehicleVerification->update([
                'status' => $status,
                'rejection_reason' => $status === DriverVehicleVerification::STATUS_REJECTED
                    ? $validated['rejection_reason']
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $this->syncVehicleAndUserStatus($vehicleVerification, $status);

            $this->notifyStatusChange($vehicleVerification, $status, $previousStatus);

            return redirect()
                ->route('vehicle-verifications.show', $vehicleVerification)
                ->with('success', 'Vehicle verification status updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update vehicle verification status', [
                'id' => $vehicleVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    private function syncVehicleAndUserStatus(DriverVehicleVerification $vehicleVerification, string $status): void
    {
        if ($status === DriverVehicleVerification::STATUS_APPROVED) {
            Vehicle::updateOrCreate(
                ['driver_id' => $vehicleVerification->user_id],
                [
                    'name' => $vehicleVerification->vehicle_name,
                    'license_plate' => $vehicleVerification->license_plate,
                    'vehicle_category_id' => $vehicleVerification->vehicle_category_id,
                    'status' => 'Active',
                ]
            );

            $vehicleVerification->user?->update([
                'status' => 'Active',
            ]);

            return;
        }

        Vehicle::where('driver_id', $vehicleVerification->user_id)->update([
            'status' => 'Inactive',
        ]);

        $vehicleVerification->user?->update([
            'status' => 'Pending',
        ]);
    }

    private function notifyStatusChange(DriverVehicleVerification $vehicleVerification, string $status, string $previousStatus): void
    {
        if ($status === $previousStatus) {
            return;
        }

        $driverName = $vehicleVerification->user?->name ?? 'Driver';
        $payload = match ($status) {
            DriverVehicleVerification::STATUS_APPROVED => [
                'title' => 'Driver Vehicle Approved',
                'message' => $driverName . ' is now fully active.',
                'type' => 'success',
            ],
            DriverVehicleVerification::STATUS_REJECTED => [
                'title' => 'Driver Vehicle Declined',
                'message' => $vehicleVerification->vehicle_name . ' verification was declined.',
                'type' => 'warning',
            ],
            default => [
                'title' => 'Driver Vehicle Pending',
                'message' => $vehicleVerification->vehicle_name . ' verification status was moved back to pending.',
                'type' => 'info',
            ],
        };

        Notification::create($payload);
    }

    public function document(DriverVehicleVerification $vehicleVerification, string $field)
    {
        $allowed = [
            'registration_card_front',
            'registration_card_back',
            'vehicle_front_photo',
            'vehicle_back_photo',
            'number_plate_photo',
            'owner_document_front',
            'owner_document_back',
        ];

        if (!in_array($field, $allowed, true)) {
            abort(404);
        }

        $path = $vehicleVerification->{$field};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
