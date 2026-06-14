<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthController extends Controller
{
    /**
     * Аутентификация администратора и выдача токена доступа
     */
    public function login(Request $request)
    {
        // Валидация входящих данных: требуем email и пароль
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Поиск пользователя по указанному email
        $user = User::where('email', $request->email)->first();

        // Обработка случая: пользователь с таким email не найден
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Пользователь не найден'],
            ]);
        }

        // Проверка соответствия пароля хешу в базе данных
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Неверный пароль'],
            ]);
        }

        // Проверка роли: доступ разрешён только пользователям с ролью 'admin'
        if ($user->role !== 'admin') {
            throw ValidationException::withMessages([
                'email' => ['Доступ запрещён. Требуется роль администратора.'],
            ]);
        }

        // Создание персонального токена доступа через Laravel Sanctum
        // Имя токена 'admin-token' используется для идентификации в панели управления токенами
        $token = $user->createToken('admin-token')->plainTextToken;

        // Формирование успешного ответа с токеном и данными пользователя
        return response()->json([
            'message' => 'Успешный вход',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Завершение сессии администратора и отзыв токена доступа
     * 
     */
    public function logout(Request $request)
    {
        // Получение текущего токена из запроса и его удаление из базы данных
        // Это немедленно инвалидирует токен для всех будущих запросов
        $request->user()->currentAccessToken()->delete();
        
        // Возврат подтверждения успешного завершения сессии
        return response()->json(['message' => 'Успешный выход']);
    }
}