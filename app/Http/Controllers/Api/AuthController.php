<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя
     * Создаёт учётную запись с ролью 'buyer' и выдаёт токен доступа
     * 
     */
    public function register(Request $request)
    {
        // Валидация входящих данных с проверкой уникальности email и сложности пароля
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => 'nullable|string|max:20',
        ]);

        // Создание записи пользователя в базе данных
        $user = User::create([
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'buyer',
        ]);

        // Генерация персонального токена доступа через Laravel Sanctum
        $token = $user->createToken('auth-token')->plainTextToken;

        // Возврат успешного ответа с токеном и данными пользователя
        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    /**
     * Аутентификация пользователя (вход в систему)
     * Проверяет учётные данные и выдаёт токен доступа
     * 
     */
    public function login(Request $request)
    {
        // Валидация входящих данных
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Поиск пользователя по указанному email
        $user = User::where('email', $request->email)->first();

        // Проверка существования пользователя и соответствия пароля
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные'],
            ]);
        }

        // Генерация токена доступа
        $token = $user->createToken('auth-token')->plainTextToken;

        // Возврат успешного ответа с токеном и данными пользователя
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
     * Завершение сессии пользователя (выход из системы)
     * Отзыв текущего токена доступа
     * 
     */
    public function logout(Request $request)
    {
        // Удаление текущего токена из базы данных для инвалидации
        $request->user()->currentAccessToken()->delete();

        // Возврат подтверждения успешного выхода
        return response()->json([
            'message' => 'Успешный выход'
        ]);
    }
}