<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Получение списка всех категорий для отображения в каталоге
     * Возвращает категории с подсчётом товаров в каждой
     * 
     *
     */
    public function index()
    {
        try {
            // Загружаем категории с количеством связанных товаров
            // withCount('products') добавляет поле products_count к каждой категории
            $categories = Category::withCount('products')->get();
            
            return response()->json($categories);
            
        } catch (\Exception $e) {
            // Логирование ошибки и возврат ответа с кодом 500
            \Log::error('API Categories index error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки категорий'], 500);
        }
    }
}