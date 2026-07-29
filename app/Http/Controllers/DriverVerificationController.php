<?php

namespace App\Http\Controllers;

use App\Models\DriverVerification;
use App\Models\Notification;
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
        try {
            if ($driverVerification->status === DriverVerification::STATUS_APPROVED) {
                return redirect()->back()->with('error', 'This verification is already approved.');
            }

            $driverVerification->update([
                'status' => DriverVerification::STATUS_APPROVED,
                'rejection_reason' => null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $driverVerification->user?->update([
                'status' => 'Pending',
                'city_id' => $driverVerification->city_id,
            ]);

            Notification::create([
                'title' => 'Driver KYC Approved',
                'message' => ($driverVerification->user?->name ?? $driverVerification->full_name) . ' KYC has been approved and is ready for vehicle verification.',
                'type' => 'success',
            ]);

            return redirect()
                ->route('driver-verifications.show', $driverVerification)
                ->with('success', 'Driver verification approved successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to approve driver verification', [
                'id' => $driverVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to approve: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, DriverVerification $driverVerification)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            if ($driverVerification->status === DriverVerification::STATUS_APPROVED) {
                return redirect()->back()->with('error', 'Approved verification cannot be rejected. Contact support if needed.');
            }

            $driverVerification->update([
                'status' => DriverVerification::STATUS_REJECTED,
                'rejection_reason' => $request->rejection_reason,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $driverVerification->user?->update([
                'status' => 'Pending',
            ]);

            Notification::create([
                'title' => 'Driver KYC Rejected',
                'message' => $driverVerification->full_name . ' verification was rejected.',
                'type' => 'warning',
            ]);

            return redirect()
                ->route('driver-verifications.show', $driverVerification)
                ->with('success', 'Driver verification rejected.');
        } catch (\Throwable $e) {
            Log::error('Failed to reject driver verification', [
                'id' => $driverVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to reject: ' . $e->getMessage());
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
}
