<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    public const CATEGORIES = [
        'school' => 'School',
        'college' => 'College',
        'university' => 'University',
        'office' => 'Office',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'category',
        'city_id',
        'address',
        'phone',
        'email',
        'status',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? ucfirst((string) $this->category);
    }

    /**
     * @return array<string, mixed>
     */
    public function toApiArray(): array
    {
        $this->loadMissing('city');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'category' => $this->category ?: 'school',
            'category_label' => $this->categoryLabel(),
            'city_id' => $this->city_id,
            'city' => $this->city?->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'status' => $this->status,
        ];
    }
}
