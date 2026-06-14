<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Обработка отправки сообщения через форму обратной связи
     * Валидирует входящие данные и создаёт запись в таблице contact_messages
     * 
     */
    public function submit(Request $request)
    {
        try {
            // Валидация входящих данных формы
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'contact' => 'required|string|max:255',
                'message' => 'required|string|max:1000',
            ]);

            // Создание записи сообщения в базе данных
            // Поле is_read устанавливается в false по умолчанию
            ContactMessage::create([
                'name' => $validated['name'],
                'contact' => $validated['contact'],
                'message' => $validated['message'],
                'is_read' => false,
            ]);

            // Возврат успешного ответа
            return response()->json([
                'message' => 'Сообщение успешно отправлено!'
            ], 200);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Возврат ошибок валидации для отображения на фронтенде
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            // Логирование непредвиденных ошибок и возврат ответа с кодом 500
            \Log::error('Contact form error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка отправки: ' . $e->getMessage()
            ], 500);
        }
    }
}