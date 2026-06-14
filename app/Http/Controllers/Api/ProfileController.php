<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


class ProfileController extends Controller
{
    /**
     * Получение данных профиля текущего авторизованного пользователя
     * 
     */
    public function show(Request $request)
    {
        // Получаем аутентифицированного пользователя из запроса
        $user = $request->user();
        
        // Возвращаем основные поля профиля в формате JSON
        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    /**
     * Обновление данных профиля текущего пользователя
     * 
     *
     */
    public function update(Request $request)
    {
        // Получаем аутентифицированного пользователя
        $user = $request->user();
        
        // Валидация входящих данных с проверкой уникальности email (исключая текущего пользователя)
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
        ]);

        // Обновление полей пользователя: используем оператор ?? для сохранения старых значений при отсутствии новых
        $user->update([
            'full_name' => $validated['full_name'] ?? $user->full_name,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        // Возвращаем обновлённые данные пользователя в формате JSON
        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }
}