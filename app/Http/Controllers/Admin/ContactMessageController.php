<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Получение списка всех сообщений с пагинацией
     */
    public function index(Request $request)
    {
        try {
            // Загружаем сообщения в обратном хронологическом порядке с пагинацией
            $messages = ContactMessage::latest()->paginate(20);
            return response()->json($messages);
        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем ответ с кодом 500
            \Log::error('Contact messages index error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки сообщений'], 500);
        }
    }

    /**
     * Отметка сообщения как прочитанного
     * 
     */
    public function markAsRead($id)
    {
        try {
            // Находим сообщение или возвращаем 404
            $message = ContactMessage::findOrFail($id);
            // Обновляем флаг is_read
            $message->update(['is_read' => true]);
            return response()->json(['message' => 'Отмечено как прочитанное']);
        } catch (\Exception $e) {
            \Log::error('Mark as read error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка обновления'], 500);
        }
    }

    /**
     * Удаление сообщения
     */
    public function destroy($id)
    {
        try {
            // Удаляем сообщение по ID
            ContactMessage::destroy($id);
            return response()->json(['message' => 'Сообщение удалено']);
        } catch (\Exception $e) {
            \Log::error('Delete message error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка удаления'], 500);
        }
    }
}