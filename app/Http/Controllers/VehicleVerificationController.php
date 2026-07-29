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
        try {
            if ($vehicleVerification->isApproved()) {
                return redirect()->back()->with('error', 'This vehicle verification is already approved.');
            }

            $vehicleVerification->update([
                'status' => DriverVehicleVerification::STATUS_APPROVED,
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

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

            Notification::create([
                'title' => 'Driver Vehicle Approved',
                'message' => ($vehicleVerification->user?->name ?? 'Driver') . ' is now fully active.',
                'type' => 'success',
            ]);

            return redirect()
                ->route('vehicle-verifications.show', $vehicleVerification)
                ->with('success', 'Vehicle verification approved successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to approve vehicle verification', [
                'id' => $vehicleVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, DriverVehicleVerification $vehicleVerification)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            if ($vehicleVerification->isApproved()) {
                return redirect()->back()->with('error', 'Approved vehicle verification cannot be rejected.');
            }

            $vehicleVerification->update([
                'status' => DriverVehicleVerification::STATUS_REJECTED,
                'rejection_reason' => $request->rejection_reason,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $vehicleVerification->user?->update([
                'status' => 'Pending',
            ]);

            Notification::create([
                'title' => 'Driver Vehicle Rejected',
                'message' => $vehicleVerification->vehicle_name . ' verification was rejected.',
                'type' => 'warning',
            ]);

            return redirect()
                ->route('vehicle-verifications.show', $vehicleVerification)
                ->with('success', 'Vehicle verification rejected.');
        } catch (\Throwable $e) {
            Log::error('Failed to reject vehicle verification', [
                'id' => $vehicleVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to reject: ' . $e->getMessage());
        }
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
