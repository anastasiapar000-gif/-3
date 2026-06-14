<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Получение списка адресов доставки текущего пользователя
     * Адреса сортируются: сначала основной (is_default), затем по дате создания
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Загрузка адресов пользователя с сортировкой
            $addresses = Address::where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json($addresses);
            
        } catch (\Exception $e) {
            // Логирование ошибки и возврат ответа с кодом 500
            \Log::error('Addresses index error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки адресов'], 500);
        }
    }

    /**
     * Добавление нового адреса доставки
     * Первый адрес пользователя автоматически становится основным
     * 
     *
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            // Валидация входящих данных адреса
            $validated = $request->validate([
                'city' => 'required|string|max:100',
                'street' => 'required|string|max:255',
                'building' => 'required|string|max:20',
                'apartment' => 'nullable|string|max:20',
                'zip_code' => 'nullable|string|max:10',
                'phone' => 'required|string|max:20',
                'delivery_comment' => 'nullable|string|max:500',
            ]);

            // Проверка: является ли этот адрес первым для пользователя
            $isFirstAddress = Address::where('user_id', $user->id)->count() === 0;

            // Создание записи адреса в базе данных
            $address = Address::create([
                'user_id' => $user->id,
                'city' => $validated['city'],
                'street' => $validated['street'],
                'building' => $validated['building'],
                'apartment' => $validated['apartment'] ?? null,
                'zip_code' => $validated['zip_code'] ?? null,
                'phone' => $validated['phone'],
                'delivery_comment' => $validated['delivery_comment'] ?? null,
                'is_default' => $isFirstAddress,
            ]);

            return response()->json([
                'message' => 'Адрес добавлен',
                'address' => $address
            ], 201);
            
        } catch (\Exception $e) {
            \Log::error('Store address error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка добавления адреса'], 500);
        }
    }

    /**
     * Установка адреса как основного (по умолчанию)
     * Сбрасывает флаг is_default у всех остальных адресов пользователя
     * 
     *
     */
    public function setDefault(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Поиск адреса с проверкой принадлежности пользователю
            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Вызов метода модели для установки адреса как основного
            $address->setAsDefault();

            return response()->json(['message' => 'Адрес установлен как основной']);
            
        } catch (\Exception $e) {
            \Log::error('Set default address error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка установки адреса'], 500);
        }
    }

    /**
     * Удаление адреса доставки
     * Запрещает удаление последнего адреса пользователя
     * 
     * 
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            // Поиск адреса с проверкой принадлежности пользователю
            $address = Address::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Проверка: нельзя удалить последний адрес пользователя
            $count = Address::where('user_id', $user->id)->count();
            if ($count <= 1) {
                return response()->json(['message' => 'Нельзя удалить последний адрес'], 422);
            }

            // Удаление записи адреса
            $address->delete();
            
            return response()->json(['message' => 'Адрес удалён']);
            
        } catch (\Exception $e) {
            \Log::error('Delete address error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка удаления адреса'], 500);
        }
    }
}