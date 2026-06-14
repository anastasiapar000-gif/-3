<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    // === КОНФИГУРАЦИЯ ===
    
    protected $table = 'carts';
    
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'size',
    ];

    // 🔧 Убрали 'size' из кастов, так как это строка в БД. 
    // quantity оставляем как integer для математики.
    protected $casts = [
        'quantity' => 'integer',
    ];

    // 🔧 Поля, которые автоматически добавляются в JSON
    protected $appends = [
        'total',             // Итоговая сумма позиции
        'formatted_total',   // Красивая сумма
        'size_label',        // Отформатированный размер
        'has_size',          // Флаг наличия размера
        'image_url',         // Ссылка на картинку товара (удобно для фронтенда)
        'product_name',      // Название товара (удобно для фронтенда)
    ];

    // === СВЯЗИ ===

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // === АКЦЕССОРЫ (Атрибуты) ===

    /**
     * Итоговая стоимость позиции
     */
    public function getTotalAttribute(): float
    {
        // Защита: если товар удален, цена 0
        return ($this->product?->price ?? 0) * $this->quantity;
    }

    /**
     * Форматированная стоимость
     */
    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, '.', ' ') . ' ₽';
    }

    /**
     * Проверка: есть ли размер
     */
    public function getHasSizeAttribute(): bool
    {
        // Проверяем, что size не null, не пустая строка и не "0"
        return !empty($this->attributes['size']) && $this->attributes['size'] !== '0';
    }

    /**
     * Форматированный размер (убираем лишние нули: 16.50 -> 16.5)
     */
    public function getSizeLabelAttribute(): ?string
    {
        if (!$this->has_size) {
            return null;
        }
        
        $size = (string) $this->attributes['size'];
        
        // Если это число с плавающей точкой, убираем лишние нули
        if (is_numeric($size)) {
            // Преобразуем в float и обратно в string, чтобы убрать .00
            return (string) (float) $size;
        }
        
        return $size;
    }

    /**
     * Ссылка на изображение товара (для удобства во Vue)
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->product || !$this->product->image) {
            return null;
        }
        // Предполагаем, что картинки лежат в storage/app/public/products
        return asset('storage/' . $this->product->image);
    }

    /**
     * Название товара (для удобства во Vue)
     */
    public function getProductNameAttribute(): ?string
    {
        return $this->product?->name;
    }
}