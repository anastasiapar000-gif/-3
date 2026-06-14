<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactMessage extends Model
{
    use SoftDeletes; 
    protected $fillable = [
        'name',
        'contact',
        'subject',
        'message',
        'is_read',
        'ip_address',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Скоуп для непрочитанных сообщений
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Пометить как прочитанное
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }
}