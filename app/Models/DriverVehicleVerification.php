<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DriverVehicleVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'vehicle_category_id',
        'vehicle_name',
        'vehicle_model',
        'vehicle_color',
        'license_plate',
        'registration_card_front',
        'registration_card_back',
        'vehicle_front_photo',
        'vehicle_back_photo',
        'number_plate_photo',
        'owner_name',
        'owner_cnic_number',
        'owner_document_front',
        'owner_document_back',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
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

    public function toApiArray(): array
    {
        $this->loadMissing('category');

        return [
            'id' => $this->id,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'vehicle_category_id' => $this->vehicle_category_id,
            'vehicle_category' => $this->category,
            'vehicle_name' => $this->vehicle_name,
            'vehicle_model' => $this->vehicle_model,
            'vehicle_color' => $this->vehicle_color,
            'license_plate' => $this->license_plate,
            'registration_card_front' => $this->documentUrl($this->registration_card_front),
            'registration_card_back' => $this->documentUrl($this->registration_card_back),
            'vehicle_front_photo' => $this->documentUrl($this->vehicle_front_photo),
            'vehicle_back_photo' => $this->documentUrl($this->vehicle_back_photo),
            'number_plate_photo' => $this->documentUrl($this->number_plate_photo),
            'owner_name' => $this->owner_name,
            'owner_cnic_number' => $this->owner_cnic_number,
            'owner_document_front' => $this->documentUrl($this->owner_document_front),
            'owner_document_back' => $this->documentUrl($this->owner_document_back),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'submitted_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
