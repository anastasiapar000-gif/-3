<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // ← ДОБАВЛЕНО: для работы токенов Sanctum

class User extends Authenticatable
{
    // ← ДОБАВЛЕНО: HasApiTokens должен быть первым
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Поля, доступные для массового присваивания
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'role',
    ];

    /**
     * Поля, скрытые при сериализации в массив/JSON
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Приведение типов атрибутов
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Laravel 11: автоматическое хеширование
    ];

    /**
     * Получить имя для аутентификации (email)
     * Laravel по умолчанию использует 'email', но можно явно указать
     */
    public function getAuthIdentifierName()
    {
        return 'email';
    }
}