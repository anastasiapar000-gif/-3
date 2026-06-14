<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    // === КОНФИГУРАЦИЯ ===
    
    // Поля для массового присваивания
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'size',
        'product_name',      // Снепшот названия
        'product_image',     // Снепшот изображения
    ];

    // 🔧 НОВОЕ: Автоматически добавлять эти доступоры в JSON
    protected $appends = [
        'total',
        'formatted_price',
        'formatted_total',
        'image_url',
        'display_name',
        'size_label',
    ];

    // Автоматическая конвертация типов
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    // === СВЯЗИ ===

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // === АКЦЕССОРЫ (Accessors) ===

    public function getTotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, '.', ' ') . ' ₽';
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, '.', ' ') . ' ₽';
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->product_image) {
            if (str_starts_with($this->product_image, 'http')) {
                return $this->product_image;
            }
            return asset('storage/' . $this->product_image);
        }
        
        if ($this->product?->images?->isNotEmpty()) {
            return $this->product->images->first()->url;
        }
        
        if ($this->product?->image) {
            return asset('storage/' . $this->product->image);
        }
        
        return null;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->product_name ?: ($this->product?->name ?? 'Товар удалён');
    }

    // === МЕТОДЫ ===

    public function hasSize(): bool
    {
        return !empty($this->size);
    }

    public function getSizeLabelAttribute(): ?string
    {
        return $this->hasSize() ? 'Размер: ' . $this->size : null;
    }
}