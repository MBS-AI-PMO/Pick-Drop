<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ParentSelfVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'account_type',
        'full_name',
        'father_name',
        'date_of_birth',
        'gender',
        'nationality',
        'address',
        'country',
        'city_id',
        'city_name',
        'complete_address',
        'postal_code',
        'cnic_number',
        'cnic_front',
        'cnic_back',
        'selfie_photo',
        'terms_accepted',
        'terms_accepted_at',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'terms_accepted' => 'boolean',
            'terms_accepted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function contactPhone(): ?string
    {
        $user = $this->user;
        if (!$user) {
            return null;
        }

        if ($user->phone) {
            return $user->phone;
        }

        $details = is_array($user->details) ? $user->details : [];

        return $details['contact'] ?? null;
    }

    public function documentUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing(['city', 'user']);

        return [
            'id' => $this->id,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'full_name' => $this->user?->name ?? $this->full_name,
            'phone' => $this->contactPhone(),
            'email' => $this->user?->email,
            'father_name' => $this->father_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'address' => $this->address,
            'country' => $this->country,
            'city_id' => $this->city_id,
            'city_name' => $this->city_name ?? $this->city?->name,
            'city' => $this->city,
            'complete_address' => $this->complete_address,
            'postal_code' => $this->postal_code,
            'cnic_number' => $this->cnic_number,
            'cnic_front' => $this->documentUrl($this->cnic_front),
            'cnic_back' => $this->documentUrl($this->cnic_back),
            'selfie_photo' => $this->documentUrl($this->selfie_photo),
            'terms_accepted' => $this->terms_accepted,
            'terms_accepted_at' => $this->terms_accepted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'submitted_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
