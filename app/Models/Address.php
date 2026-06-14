<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Address extends Model
{
    use SoftDeletes;

    // Поля для массового присваивания
    protected $fillable = [
        'user_id',
        'city',
        'street',
        'building',
        'apartment',
        'zip_code',
        'phone',
        'delivery_comment',
        'is_default',
    ];

    // Автоматическая конвертация типов
    protected $casts = [
        'is_default' => 'boolean',
        'deleted_at' => 'datetime', // ← Добавлено для soft deletes
    ];

    // === СВЯЗИ ===
    
    /**
     * Связь с пользователем
     * второй аргумент — это внешний ключ в ТЕКУЩЕЙ таблице
     * Третий аргумент (владелец) не нужен, если в users первичный ключ — 'id'
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
        // Или просто: return $this->belongsTo(User::class);
    }

    // === СКОУПЫ (для удобных запросов) ===

    /**
     * Скоуп для получения только дефолтных адресов
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Скоуп для получения адресов конкретного пользователя
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // === ЛОГИКА ДЛЯ is_default ===

    /**
     * Установить этот адрес как адрес по умолчанию
     *  добавлена транзакция для атомарности
     */
    public function setAsDefault(): void
    {
        DB::transaction(function () {
            // Снимаем флаг со ВСЕХ адресов этого пользователя (включая удалённые, если нужно)
            static::withoutGlobalScopes()
                ->where('user_id', $this->user_id)
                ->where('id', '!=', $this->id)
                ->update(['is_default' => false]);
            
            // Устанавливаем текущему
            $this->is_default = true;
            $this->save();
        });
    }

    /**
     * Получить дефолтный адрес пользователя (статический метод)
     */
    public static function getDefaultForUser($userId): ?self
    {
        return static::forUser($userId)->default()->first();
    }

    // === АКЦЕССОРЫ (форматирование вывода) ===

    /**
     * Получить полный адрес одной строкой
     * обработка null-значений
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->zip_code,      // Индекс в начале — стандарт РФ
            $this->city,
            $this->street,
            $this->building ? 'д. ' . $this->building : null,
            $this->apartment ? 'кв. ' . $this->apartment : null,
        ]);

        return implode(', ', $parts) ?: 'Адрес не указан';
    }

    /**
     * Получить краткий адрес (для выпадающих списков)
     */
    public function getShortAddressAttribute(): string
    {
        return collect([
            $this->city,
            $this->street,
            $this->building ? $this->building : null,
        ])->filter()->implode(', ');
    }
}