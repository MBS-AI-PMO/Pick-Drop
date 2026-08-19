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
        'referral_code',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'details' => 'array',
            'service_areas' => 'array',
        ];
    }
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'parent_id');
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

        $kycStatus = $this->kycStatus();
        if ($kycStatus !== 'approved') {
            return match ($kycStatus) {
                'pending' => 'kyc_pending',
                'rejected' => 'kyc_resubmit',
                default => 'kyc_submit',
            };
        }

        $vehicleStatus = $this->vehicleVerificationStatus();

        return match ($vehicleStatus) {
            'approved' => 'dashboard',
            'pending' => 'vehicle_verification_pending',
            'rejected' => 'vehicle_verification_resubmit',
            default => 'vehicle_verification_submit',
        };
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
        $base['verification'] = $this->driverVerification
            ? $this->driverVerification->toApiArray()
            : null;
        $base['vehicle_verification'] = $this->vehicleVerification
            ? $this->vehicleVerification->toApiArray()
            : null;

        return $base;
    }
}
