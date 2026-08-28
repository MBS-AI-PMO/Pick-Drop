<?php

namespace App\Http\Controllers;

use App\Mail\AccountVerificationRejectedMail;
use App\Mail\AccountVerifiedMail;
use App\Models\Notification;
use App\Models\ParentSelfVerification;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ParentSelfVerificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ParentSelfVerification::with(['user', 'city', 'reviewer'])
                ->latest();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('type')) {
                $query->where('account_type', $request->type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('cnic_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            }

            $verifications = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

            return view('pickdrop.parent-self-verifications.index', compact('verifications'));
        } catch (\Throwable $e) {
            Log::error('Failed to load parent/self verifications', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load verifications: ' . $e->getMessage());
        }
    }

    public function show(ParentSelfVerification $parentSelfVerification)
    {
        $parentSelfVerification->load([
            'user.students.city',
            'user.students.pickupArea',
            'user.commuteProfile.city',
            'user.commuteProfile.pickupArea',
            'user.commuteProfile.dropArea',
            'city',
            'reviewer',
        ]);

        return view('pickdrop.parent-self-verifications.show', [
            'verification' => $parentSelfVerification,
        ]);
    }

    public function approve(Request $request, ParentSelfVerification $parentSelfVerification)
    {
        $request->merge(['status' => ParentSelfVerification::STATUS_APPROVED]);

        return $this->updateStatus($request, $parentSelfVerification);
    }

    public function reject(Request $request, ParentSelfVerification $parentSelfVerification)
    {
        $request->merge(['status' => ParentSelfVerification::STATUS_REJECTED]);

        return $this->updateStatus($request, $parentSelfVerification);
    }

    public function updateStatus(Request $request, ParentSelfVerification $parentSelfVerification)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:1000'],
        ]);

        try {
            $status = $validated['status'];
            $previousStatus = $parentSelfVerification->status;

            $parentSelfVerification->update([
                'status' => $status,
                'rejection_reason' => $status === ParentSelfVerification::STATUS_REJECTED
                    ? $validated['rejection_reason']
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            $parentSelfVerification->user?->update(
                $this->userStatusPayload($parentSelfVerification, $status)
            );

            $this->notifyStatusChange($parentSelfVerification, $status, $previousStatus);
            $this->emailStatusChange($parentSelfVerification, $status, $previousStatus);

            return redirect()
                ->route('parent-self-verifications.show', $parentSelfVerification)
                ->with('success', 'Verification status updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update parent/self verification status', [
                'id' => $parentSelfVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }

    private function userStatusPayload(ParentSelfVerification $verification, string $status): array
    {
        if ($status === ParentSelfVerification::STATUS_APPROVED) {
            return [
                'status' => 'Active',
                'city_id' => $verification->city_id,
            ];
        }

        return [
            'status' => 'Pending',
        ];
    }

    private function notifyStatusChange(ParentSelfVerification $verification, string $status, string $previousStatus): void
    {
        if ($status === $previousStatus) {
            return;
        }

        $name = $verification->user?->name ?? $verification->full_name;
        $label = strcasecmp((string) $verification->account_type, 'self') === 0 ? 'Self' : 'Parent';
        $payload = match ($status) {
            ParentSelfVerification::STATUS_APPROVED => [
                'title' => $label . ' KYC Approved',
                'message' => $name . ' identity verification has been approved.',
                'type' => 'success',
            ],
            ParentSelfVerification::STATUS_REJECTED => [
                'title' => $label . ' KYC Declined',
                'message' => $name . ' identity verification was declined.',
                'type' => 'warning',
            ],
            default => [
                'title' => $label . ' KYC Pending',
                'message' => $name . ' verification status was moved back to pending.',
                'type' => 'info',
            ],
        };

        Notification::create($payload);
    }

    private function emailStatusChange(ParentSelfVerification $verification, string $status, string $previousStatus): void
    {
        if ($status === $previousStatus) {
            return;
        }

        $user = $verification->user;
        if (!$user?->email) {
            return;
        }

        try {
            if ($status === ParentSelfVerification::STATUS_APPROVED) {
                Mail::to($user->email)->send(new AccountVerifiedMail($user));
            } elseif ($status === ParentSelfVerification::STATUS_REJECTED) {
                Mail::to($user->email)->send(new AccountVerificationRejectedMail(
                    $user,
                    (string) $verification->rejection_reason
                ));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send parent/self verification email', [
                'id' => $verification->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function document(ParentSelfVerification $parentSelfVerification, string $field)
    {
        $allowed = [
            'cnic_front',
            'cnic_back',
            'selfie_photo',
        ];

        if (!in_array($field, $allowed, true)) {
            abort(404);
        }

        $path = $parentSelfVerification->{$field};

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }
}
