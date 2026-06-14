<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    /**
     * Загрузка изображений для товара
     * Сохраняет файлы в папку по категории товара
     * 
     * 
     */
    public function store(Request $request, $productId)
    {
        try {
            // Валидация загружаемых файлов
            $request->validate([
                'images' => 'required|array|min:1|max:4',
                'images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            ], [
                'images.required' => 'Выберите хотя бы одно изображение',
                'images.max' => 'Можно загрузить не более 4 изображений',
                'images.array' => 'Неверный формат данных',
                'images.*.image' => 'Все файлы должны быть изображениями',
                'images.*.mimes' => 'Разрешены только форматы: JPEG, PNG, JPG, WebP, GIF',
                'images.*.max' => 'Размер файла не должен превышать 5MB',
            ]);

            // Получаем товар с данными категории для определения папки
            $product = Product::with('category')->findOrFail($productId);

            // Формируем путь к папке на основе slug категории
            $categorySlug = $product->category?->slug ?? 'other';
            $folderName = Str::slug($categorySlug);
            $directory = 'products/' . $folderName;

            $uploadedImages = [];
            // Получаем текущий максимальный порядок для продолжения нумерации
            $startOrder = ProductImage::where('product_id', $product->id)->max('sort_order') ?? -1;

            foreach ($request->file('images') as $index => $file) {
                // Генерация уникального имени файла
                $filename = time() . '_' . ($startOrder + $index + 1) . '.' . $file->getClientOriginalExtension();
                
                // Сохранение файла в хранилище
                $path = $file->storeAs($directory, $filename, 'public');

                if (!$path) {
                    throw new \Exception("Не удалось сохранить файл: " . $file->getClientOriginalName());
                }

                // Создание записи в базе данных
                $image = ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'sort_order' => $startOrder + $index + 1,
                    // Первое изображение становится главным, если других нет
                    'is_main' => ProductImage::where('product_id', $product->id)->count() === 0, 
                    'alt_text' => $product->name,
                ]);

                $uploadedImages[] = $image;
            }

            return response()->json([
                'success' => true,
                'message' => 'Изображения успешно загружены (' . count($uploadedImages) . ' шт.)',
                'images' => $uploadedImages,
                'count' => count($uploadedImages),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('ProductImageController store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке изображений: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Получение списка всех изображений товара
     * 
     
     */
    public function index($productId)
    {
        try {
            // Загрузка изображений с сортировкой по порядку отображения
            $images = ProductImage::where('product_id', $productId)
                ->orderBy('sort_order', 'asc')
                ->get()
                ->map(function($image) {
                    return [
                        'id' => $image->id,
                        'path' => $image->path,
                        'url' => $image->path ? asset('storage/' . $image->path) : '',
                        'sort_order' => $image->sort_order,
                        'is_main' => (bool) $image->is_main,
                        'alt_text' => $image->alt_text,
                        'created_at' => $image->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'images' => $images,
                'count' => $images->count(),
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductImageController index error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке списка изображений: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Удаление изображения товара
     * Удаляет файл с диска и запись из базы данных
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $image = ProductImage::findOrFail($id);

            // Удаление физического файла из хранилища
            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            // Удаление записи из базы данных
            $image->delete();

            return response()->json([
                'success' => true,
                'message' => 'Изображение успешно удалено',
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductImageController destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при удалении: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Установка изображения как главного для товара
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function setMain($id)
    {
        try {
            $image = ProductImage::findOrFail($id);

            // Сброс флага is_main у всех изображений этого товара
            ProductImage::where('product_id', $image->product_id)
                ->update(['is_main' => false]);

            // Установка флага для выбранного изображения
            $image->update(['is_main' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Главное изображение установлено',
                'image' => $image,
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductImageController setMain error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Обновление порядка изображений 
     * 
     */
    public function reorder(Request $request, $productId)
    {
        try {
            $request->validate([
                'images' => 'required|array',
                'images.*.id' => 'required|exists:product_images,id',
                'images.*.sort_order' => 'required|integer',
            ]);

            // Обновление порядка сортировки для каждого изображения
            foreach ($request->images as $imageData) {
                ProductImage::where('id', $imageData['id'])
                    ->where('product_id', $productId)
                    ->update(['sort_order' => $imageData['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Порядок изображений обновлен',
            ]);

        } catch (\Exception $e) {
            \Log::error('ProductImageController reorder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при сортировке: ' . $e->getMessage(),
            ], 500);
        }
    }
}