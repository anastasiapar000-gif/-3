<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'delivery_method',
        'delivery_price',
        'cdek_city',
        'cdek_address',
        'address',
        'entrance',
        'floor',
        'intercom',
        'pickup_address',
        'phone',
        'payment_method',
        'comment',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'delivery_price' => 'decimal:2',
    ];

    // === СВЯЗИ ===
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // === АВТО-ГЕНЕРАЦИЯ ORDER_NUMBER ===
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                // Формат: ORD-YYYYMMDD-XXXX
                $date = date('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $order->order_number = 'ORD-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    // === АКЦЕССОРЫ ДЛЯ СТАТУСОВ ===
    
    public function getStatusLabelAttribute(): string
    {
        return [
            'pending' => 'Ожидает оплаты',
            'confirmed' => 'Подтверждён',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменён',
        ][$this->status] ?? $this->status;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}