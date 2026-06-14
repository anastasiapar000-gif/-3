<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class CategoryController extends Controller
{
    /**
     * Получение списка всех категорий
     * Возвращает категории с количеством товаров в каждой
     */
    public function index()
    {
        try {
            // Загружаем категории с подсчётом связанных товаров, сортируем по имени
            $categories = Category::withCount('products')
                ->orderBy('name')
                ->get();
                
            return response()->json($categories);
            
        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем ответ с кодом 500
            \Log::error('Categories index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки категорий: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Создание новой категории
     */
    public function store(Request $request)
    {
        try {
            // Валидация входящих данных с проверкой уникальности
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')
                ],
                'slug' => 'nullable|string|max:255|unique:categories,slug',
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Название категории обязательно',
                'name.unique' => 'Категория с таким названием уже существует',
                'slug.unique' => 'Такой URL уже используется',
            ]);

            // Автоматическая генерация slug из названия, если не передан
            $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

            // Создание записи в базе данных
            $category = Category::create($validated);
            
            return response()->json([
                'message' => 'Категория успешно создана',
                'category' => $category
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Возврат ошибок валидации для отображения на фронтенде
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Category store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка создания: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление существующей категорииe
     */
    public function update(Request $request, $id)
    {
        try {
            // Проверка существования категории
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json(['message' => 'Категория не найдена'], 404);
            }
            
            // Валидация с игнорированием текущего ID при проверке уникальности
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'name')->ignore($category->id)
                ],
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('categories', 'slug')->ignore($category->id)
                ],
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Название категории обязательно',
                'name.unique' => 'Категория с таким названием уже существует',
                'slug.unique' => 'Такой URL уже используется',
            ]);

            // Генерация slug при изменении названия или отсутствии slug
            if (empty($validated['slug']) || $request->has('name')) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            // Обновление записи в базе данных
            $category->update($validated);
            
            // Возврат актуальных данных категории
            return response()->json([
                'message' => 'Категория успешно обновлена',
                'category' => $category->fresh()
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Category update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка обновления: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление категории
     * 
     */
    public function destroy($id)
    {
        try {
            $category = Category::find($id);
            
            if (!$category) {
                return response()->json(['message' => 'Категория не найдена'], 404);
            }
            
            // Проверка наличия связанных товаров перед удалением
            $productsCount = $category->products()->count();
            
            if ($productsCount > 0) {
                return response()->json([
                    'message' => 'Нельзя удалить категорию, в которой есть товары',
                    'products_count' => $productsCount
                ], 422);
            }
            
            // Удаление категории
            $category->delete();
            
            return response()->json(['message' => 'Категория успешно удалена']);
            
        } catch (\Exception $e) {
            \Log::error('Category destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка удаления: ' . $e->getMessage()
            ], 500);
        }
    }
}