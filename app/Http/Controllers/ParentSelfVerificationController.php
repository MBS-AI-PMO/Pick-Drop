<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesVerificationDocuments;
use App\Mail\AccountVerificationRejectedMail;
use App\Mail\AccountVerifiedMail;
use App\Models\City;
use App\Models\Notification;
use App\Models\ParentSelfVerification;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ParentSelfVerificationController extends Controller
{
    use ManagesVerificationDocuments;

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

    public function edit(ParentSelfVerification $parentSelfVerification)
    {
        $parentSelfVerification->load(['user', 'city']);
        $cities = City::active()->orderBy('name')->get(['id', 'name']);

        return view('pickdrop.parent-self-verifications.edit', [
            'verification' => $parentSelfVerification,
            'cities' => $cities,
        ]);
    }

    public function update(Request $request, ParentSelfVerification $parentSelfVerification)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
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
                Rule::unique('parent_self_verifications', 'cnic_number')->ignore($parentSelfVerification->id),
            ],
            'cnic_front' => ['nullable', 'image', 'max:5120'],
            'cnic_back' => ['nullable', 'image', 'max:5120'],
            'selfie_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        try {
            $city = City::find((int) $validated['city_id']);
            $payload = [
                'full_name' => $validated['full_name'],
                'father_name' => $validated['father_name'] ?? null,
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'] ?? null,
                'nationality' => $validated['nationality'] ?? $parentSelfVerification->nationality,
                'address' => $validated['address'],
                'country' => $validated['country'] ?? $parentSelfVerification->country,
                'city_id' => (int) $validated['city_id'],
                'city_name' => $city?->name,
                'complete_address' => $validated['complete_address'] ?? $validated['address'],
                'postal_code' => $validated['postal_code'] ?? null,
                'cnic_number' => $this->normalizeCnicNumber($validated['cnic_number']),
            ];

            $documentUpdates = $this->replaceVerificationDocuments(
                $parentSelfVerification,
                $request->allFiles(),
                'parent-self-kyc/' . $parentSelfVerification->user_id,
                [
                    'cnic_front' => 'cnic_front',
                    'cnic_back' => 'cnic_back',
                    'selfie_photo' => 'selfie',
                ]
            );

            $parentSelfVerification->update(array_merge($payload, $documentUpdates));

            $user = $parentSelfVerification->user;
            if ($user) {
                $details = is_array($user->details) ? $user->details : [];
                $details['address'] = $validated['address'];
                $user->update([
                    'name' => $validated['full_name'],
                    'city_id' => (int) $validated['city_id'],
                    'details' => $details,
                ]);
            }

            return redirect()
                ->route('parent-self-verifications.show', $parentSelfVerification)
                ->with('success', 'Verification details updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update parent/self verification details', [
                'id' => $parentSelfVerification->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update verification: ' . $e->getMessage());
        }
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

            $parentSelfVerification->forceFill([
                'status' => $status,
                'rejection_reason' => $status === ParentSelfVerification::STATUS_REJECTED
                    ? ($validated['rejection_reason'] ?? null)
                    : null,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ])->save();

            $user = $parentSelfVerification->user()->first();
            if ($user) {
                $user->forceFill(
                    $this->userStatusPayload($parentSelfVerification->fresh(), $status)
                )->save();
            }

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

    public function destroy(ParentSelfVerification $parentSelfVerification)
    {
        $parentSelfVerification->delete();

        return redirect()
            ->route('parent-self-verifications.index')
            ->with('success', 'Parent / self verification deleted.');
    }
}
