<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str; 

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'material_id',
        'stone_id',
        'name',
        'slug',        
        'description',
        'price',
        'sizes',        // JSON: {"15.5": 3, "16": 0, "17": 5}
        'stock',        // Общий остаток (для товаров без размеров или как сумма sizes)
        'image', 
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_featured' => 'boolean',
        'sizes' => 'array',  // Автоматически: JSON ↔ массив
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $baseSlug = Str::slug($product->name);
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $product->slug = $slug;
            }
            
            // Инициализируем sizes пустым массивом, если он не передан
            if (!isset($product->sizes)) {
                $product->sizes = [];
            }
        });

        static::deleting(function ($product) {
            foreach ($product->images as $image) {
                $image->delete();
            }
        });
    }

    // === СВЯЗИ ===

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function stone(): BelongsTo
    {
        return $this->belongsTo(Stone::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    // === МЕТОДЫ ДЛЯ РАЗМЕРОВ ===

    /**
     * Проверка: есть ли у товара размеры (например, кольцо)
     */
    public function hasSizes(): bool
    {
        return !empty($this->sizes) && is_array($this->sizes);
    }

    /**
     * Получить количество конкретного размера
     */
    public function getSizeQuantity(string $size): int
    {
        if (!$this->hasSizes() || !isset($this->sizes[$size])) {
            return 0;
        }
        return (int) $this->sizes[$size];
    }

    /**
     * Проверка: доступен ли конкретный размер (количество > 0)
     */
    public function isSizeAvailable(string $size): bool
    {
        return $this->getSizeQuantity($size) > 0;
    }

    /**
     * Получить список доступных размеров (где количество > 0)
     */
    public function getAvailableSizesAttribute(): array
    {
        if (!$this->sizes) {
            return [];
        }
        
        return collect($this->sizes)
            ->filter(fn($qty) => $qty > 0)
            ->keys()
            ->sort(fn($a, $b) => floatval($a) <=> floatval($b))
            ->values()
            ->toArray();
    }

    /**
     * 🔧 ВАЖНО: Уменьшить количество конкретного размера (при покупке)
     * Использует правильное присваивание, чтобы избежать ошибки "Indirect modification"
     */
    public function decrementSize(string $size, int $quantity = 1): bool
    {
        // Получаем текущий массив размеров
        $currentSizes = $this->sizes ?? [];
        
        // Проверяем наличие и достаточное количество
        if (!isset($currentSizes[$size]) || $currentSizes[$size] < $quantity) {
            return false;
        }

        // Уменьшаем
        $currentSizes[$size] -= $quantity;
        
        // Опционально: удаляем размер, если стал 0, чтобы не засорять JSON
        if ($currentSizes[$size] <= 0) {
            unset($currentSizes[$size]);
        }

        // 🔥 КЛЮЧЕВОЙ МОМЕНТ: Присваиваем измененный массив обратно атрибуту модели
        $this->sizes = $currentSizes;
        
        // Обновляем общий stock как сумму всех размеров
        $this->stock = array_sum($currentSizes);

        return $this->save();
    }

    /**
     * 🔧 ВАЖНО: Увеличить количество конкретного размера (при возврате)
     */
    public function incrementSize(string $size, int $quantity = 1): bool
    {
        $currentSizes = $this->sizes ?? [];
        
        // Если размера нет, создаем его
        if (!isset($currentSizes[$size])) {
            $currentSizes[$size] = 0;
        }

        $currentSizes[$size] += $quantity;

        // 🔥 Присваиваем обратно
        $this->sizes = $currentSizes;
        
        // Обновляем общий stock
        $this->stock = array_sum($currentSizes);

        return $this->save();
    }

    /**
     * Для обычных товаров (без размеров): уменьшить общий stock
     */
    public function decrementStock(int $quantity = 1): bool
    {
        if ($this->stock < $quantity) {
            return false;
        }
        $this->stock -= $quantity;
        return $this->save();
    }

    /**
     * Для обычных товаров (без размеров): увеличить общий stock
     */
    public function incrementStock(int $quantity = 1): bool
    {
        $this->stock += $quantity;
        return $this->save();
    }
}