<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): bool
    {
        if ($this->is_read) {
            return false;
        }

        return $this->update(['is_read' => true]);
    }

    public static function unreadCount(): int
    {
        return static::unread()->count();
    }
}
