<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DriverVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'full_name',
        'father_name',
        'date_of_birth',
        'address',
        'city_id',
        'cnic_number',
        'cnic_front',
        'cnic_back',
        'selfie_photo',
        'license_number',
        'license_front',
        'license_back',
        'license_expiry',
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
            'license_expiry' => 'date',
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
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'full_name' => $this->user?->name ?? $this->full_name,
            'phone' => $this->user?->phone,
            'email' => $this->user?->email,
            'father_name' => $this->father_name,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'address' => $this->address,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'cnic_number' => $this->cnic_number,
            'cnic_front' => $this->documentUrl($this->cnic_front),
            'cnic_back' => $this->documentUrl($this->cnic_back),
            'selfie_photo' => $this->documentUrl($this->selfie_photo),
            'license_number' => $this->license_number,
            'license_front' => $this->documentUrl($this->license_front),
            'license_back' => $this->documentUrl($this->license_back),
            'license_expiry' => $this->license_expiry?->format('Y-m-d'),
            'terms_accepted' => $this->terms_accepted,
            'terms_accepted_at' => $this->terms_accepted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'submitted_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
