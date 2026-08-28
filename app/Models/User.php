<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'details',
        'city_id',
        'service_areas',
        'otp',
        'phone_otp',
        'phone_otp_expires_at',
        'phone_verified_at',
        'referral_code',
        'email_verified_at',
        'last_lat',
        'last_lng',
        'last_location_at',
        'last_ride_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'referred_by',
        'referral_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'phone_otp',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'phone_otp_expires_at' => 'datetime',
            'last_location_at' => 'datetime',
            'last_lat' => 'float',
            'last_lng' => 'float',
            'password' => 'hashed',
            'details' => 'array',
            'service_areas' => 'array',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return strcasecmp(trim((string) $this->role), 'Super Admin') === 0;
    }

    public function isPanelAdmin(): bool
    {
        $role = strtolower(trim((string) $this->role));

        return in_array($role, ['admin', 'super admin'], true);
    }

    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    public static function ensureSuperAdminExists(): void
    {
        if (static::whereRaw('LOWER(TRIM(role)) = ?', ['super admin'])->exists()) {
            return;
        }

        $firstAdmin = static::where('role', 'Admin')->orderBy('id')->first();

        if ($firstAdmin) {
            $firstAdmin->update(['role' => 'Super Admin']);
        }
    }
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function assignedVehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    public function driverVerification()
    {
        return $this->hasOne(DriverVerification::class);
    }

    public function vehicleVerification()
    {
        return $this->hasOne(DriverVehicleVerification::class);
    }

    public function parentSelfVerification()
    {
        return $this->hasOne(ParentSelfVerification::class);
    }

    public function commuteProfile()
    {
        return $this->hasOne(SelfCommuteProfile::class);
    }

    public function needsPhoneVerification(): bool
    {
        return filled($this->phone) && is_null($this->phone_verified_at);
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function isParentAccount(): bool
    {
        return strcasecmp(trim((string) $this->role), 'parent') === 0;
    }

    public function isSelfAccount(): bool
    {
        return strcasecmp(trim((string) $this->role), 'self') === 0;
    }

    public function isParentSelf(): bool
    {
        return $this->isParentAccount() || $this->isSelfAccount();
    }

    public function parentSelfKycStatus(): string
    {
        return $this->parentSelfVerification?->status ?? 'not_submitted';
    }

    public function hasChildren(): bool
    {
        if ($this->relationLoaded('students')) {
            return $this->students->isNotEmpty();
        }

        return $this->students()->exists();
    }

    public function hasCommuteProfile(): bool
    {
        if ($this->relationLoaded('commuteProfile')) {
            return $this->commuteProfile !== null;
        }

        return $this->commuteProfile()->exists();
    }

    public function isParentSelfOnboardingComplete(): bool
    {
        if ($this->parentSelfKycStatus() !== 'approved') {
            return false;
        }

        if ($this->isSelfAccount()) {
            return $this->hasCommuteProfile();
        }

        if ($this->isParentAccount()) {
            return $this->hasChildren();
        }

        return false;
    }

    public function parentSelfNextStep(): string
    {
        if (is_null($this->email_verified_at)) {
            return 'verify_email';
        }

        if ($this->needsPhoneVerification()) {
            return 'verify_phone';
        }

        $kycStatus = $this->parentSelfKycStatus();
        if ($kycStatus !== 'approved') {
            return match ($kycStatus) {
                'pending' => 'kyc_pending',
                'rejected' => 'kyc_resubmit',
                default => 'kyc_submit',
            };
        }

        if ($this->isSelfAccount()) {
            if (!$this->hasCommuteProfile()) {
                return 'setup_locations';
            }

            return $this->hasPickupRequests() ? 'dashboard' : 'create_request';
        }

        if (!$this->hasChildren()) {
            return 'add_children';
        }

        return $this->hasPickupRequests() ? 'dashboard' : 'create_request';
    }

    public function pickupRequests()
    {
        return $this->hasMany(PickupRequest::class, 'parent_id');
    }

    public function hasPickupRequests(): bool
    {
        if ($this->relationLoaded('pickupRequests')) {
            return $this->pickupRequests
                ->whereNotIn('status', ['cancelled'])
                ->isNotEmpty();
        }

        return $this->pickupRequests()->whereNotIn('status', ['cancelled'])->exists();
    }

    /**
     * Parent / Self API payload with onboarding status.
     *
     * @return array<string, mixed>
     */
    public function toParentSelfApiArray(): array
    {
        $this->loadMissing([
            'city',
            'parentSelfVerification.city',
            'commuteProfile.city',
            'commuteProfile.pickupArea',
            'commuteProfile.dropArea',
        ]);

        $details = is_array($this->details) ? $this->details : [];
        $base = $this->toArray();
        $base['address'] = $details['address'] ?? null;
        $base['contact'] = $this->phone ?? ($details['contact'] ?? null);
        $base['account_type'] = strtolower(trim((string) $this->role));
        $base['kyc_status'] = $this->parentSelfKycStatus();
        $base['next_step'] = $this->parentSelfNextStep();
        $base['onboarding_complete'] = $this->isParentSelfOnboardingComplete();
        $base['phone_verified'] = $this->phone_verified_at !== null;
        $base['needs_phone_verification'] = $this->needsPhoneVerification();
        $base['verification'] = $this->parentSelfVerification
            ? $this->parentSelfVerification->toApiArray()
            : null;
        $base['commute_profile'] = $this->isSelfAccount()
            ? $this->commuteProfile?->toApiArray()
            : null;
        $base['children_count'] = $this->isParentAccount()
            ? ($this->relationLoaded('students') ? $this->students->count() : $this->students()->count())
            : 0;

        return $base;
    }

    public function kycStatus(): string
    {
        return $this->driverVerification?->status ?? 'not_submitted';
    }

    public function vehicleVerificationStatus(): string
    {
        return $this->vehicleVerification?->status ?? 'not_submitted';
    }

    public function hasServiceAreas(): bool
    {
        return count($this->service_areas ?? []) > 0;
    }

    public function isOnboardingComplete(): bool
    {
        return $this->kycStatus() === 'approved'
            && $this->vehicleVerificationStatus() === 'approved'
            && $this->hasServiceAreas();
    }

    public function driverCityId(): ?int
    {
        if ($this->city_id) {
            return (int) $this->city_id;
        }

        return $this->driverVerification?->city_id
            ? (int) $this->driverVerification->city_id
            : null;
    }

    public function driverNextStep(): string
    {
        if (is_null($this->email_verified_at)) {
            return 'verify_email';
        }

        if ($this->needsPhoneVerification()) {
            return 'verify_phone';
        }

        $kycStatus = $this->kycStatus();
        if ($kycStatus !== 'approved') {
            return match ($kycStatus) {
                'pending' => 'kyc_pending',
                'rejected' => 'kyc_resubmit',
                default => 'kyc_submit',
            };
        }

        $vehicleStatus = $this->vehicleVerificationStatus();
        if ($vehicleStatus !== 'approved') {
            return match ($vehicleStatus) {
                'pending' => 'vehicle_verification_pending',
                'rejected' => 'vehicle_verification_resubmit',
                default => 'vehicle_verification_submit',
            };
        }

        if (!$this->hasServiceAreas()) {
            return 'setup_service_areas';
        }

        return 'dashboard';
    }

    /**
     * Driver API: city relation + service_areas as full area rows (ids stored on user).
     *
     * @return array<string, mixed>
     */
    public function toDriverApiArray(): array
    {
        $this->loadMissing(['city', 'assignedVehicle.category', 'driverVerification.city', 'vehicleVerification.category']);

        $areaIds = array_values(array_unique(array_map('intval', $this->service_areas ?? [])));

        /** @var Collection<int, Area> $byId */
        $byId = $areaIds === []
            ? new Collection
            : Area::whereIn('id', $areaIds)->get()->keyBy('id');

        $map = static function (array $idList) use ($byId): array {
            return collect($idList)
                ->map(fn (int $id) => $byId->get($id))
                ->filter()
                ->values()
                ->all();
        };

        $base = $this->toArray();
        $base['city'] = $this->city;
        $base['vehicle'] = $this->assignedVehicle;
        $base['service_areas'] = $map(array_map('intval', $this->service_areas ?? []));
        $base['kyc_status'] = $this->kycStatus();
        $base['vehicle_verification_status'] = $this->vehicleVerificationStatus();
        $base['service_areas_setup'] = $this->hasServiceAreas();
        $base['onboarding_complete'] = $this->isOnboardingComplete();
        $base['next_step'] = $this->driverNextStep();
        $base['phone_verified'] = $this->phone_verified_at !== null;
        $base['needs_phone_verification'] = $this->needsPhoneVerification();
        $base['last_lat'] = $this->last_lat;
        $base['last_lng'] = $this->last_lng;
        $base['last_location_at'] = $this->last_location_at?->toIso8601String();
        $base['verification'] = $this->driverVerification
            ? $this->driverVerification->toApiArray()
            : null;
        $base['vehicle_verification'] = $this->vehicleVerification
            ? $this->vehicleVerification->toApiArray()
            : null;

        return $base;
    }
}
