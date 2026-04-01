<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'push_enabled',
        'email_enabled',
        'new_messages',
        'child_activity',
        'school_alerts',
        'payment_reminders',
        'weekly_updates',
        'promotions',
    ];

    protected $casts = [
        'push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'new_messages' => 'boolean',
        'child_activity' => 'boolean',
        'school_alerts' => 'boolean',
        'payment_reminders' => 'boolean',
        'weekly_updates' => 'boolean',
        'promotions' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

