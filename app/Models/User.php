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

    /**
     * Driver API: city relation + service_areas as full area rows (ids stored on user).
     *
     * @return array<string, mixed>
     */
    public function toDriverApiArray(): array
    {
        $this->loadMissing(['city', 'assignedVehicle.category']);

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

        return $base;
    }
}
