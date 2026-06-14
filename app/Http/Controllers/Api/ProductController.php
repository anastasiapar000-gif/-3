<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    /**
     * Получение списка товаров для каталога с поддержкой фильтрации и сортировки
     * 
     * Поддерживаемые параметры запроса:
     * - category_id: фильтрация по категории
     * - min_price / max_price: фильтрация по диапазону цен
     * - search: поиск по названию и описанию
     * - sort: сортировка (price_asc, price_desc, name_asc, name_desc, newest)
     * - per_page: количество товаров на странице (по умолчанию 12)
     * 
     *
     */
    public function index(Request $request)
    {
        // Загружаем связанные модели для оптимизации количества запросов к БД
        $query = Product::with(['category', 'material', 'stone', 'images']);

        // === ПРИМЕНЕНИЕ ФИЛЬТРОВ ===
        
        // Фильтрация по категории
        if ($request->has('category_id') && $request->category_id !== '') {
            $query->where('category_id', $request->category_id);
        }

        // Фильтрация по максимальной цене
        if ($request->has('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }
        
        // Фильтрация по минимальной цене
        if ($request->has('min_price') && is_numeric($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }

        // Поиск по названию или описанию товара
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // === ПРИМЕНЕНИЕ СОРТИРОВКИ ===
        $sortBy = $request->get('sort', 'newest');
        
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                // По умолчанию: новые товары первыми
                $query->latest();
        }

        // Пагинация результатов
        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // === ФОРМАТИРОВАНИЕ ОТВЕТА ДЛЯ КЛИЕНТА ===
        $products->getCollection()->transform(function ($product) {
            $data = $product->toArray();

            // Обработка поля sizes: гарантируем, что это массив, а не строка JSON
            if (is_string($data['sizes'])) {
                $decoded = json_decode($data['sizes'], true);
                $data['sizes'] = $decoded ?: [];
            } elseif ($data['sizes'] === null) {
                $data['sizes'] = [];
            }

            // Обработка изображений: добавляем URL первого изображения для превью
            if ($product->images && $product->images->count() > 0) {
                $firstImage = $product->images->first();
                $data['image_url'] = asset('storage/' . $firstImage->path);
                $data['image'] = $firstImage->path;
            } else {
                $data['image_url'] = null;
                $data['image'] = null;
            }

            return $data;
        });

        return response()->json($products);
    }

    /**
     * Получение детальной информации о товаре по slug
     * 
     * 
     */
    public function show($slug)
    {
        try {
            // Декодируем slug для поддержки кириллицы и спецсимволов в URL
            $slug = urldecode($slug);
            
            Log::info("API Request: Product show for slug: {$slug}");
            
            // Загрузка товара с связанными моделями
            $product = Product::with(['category', 'material', 'stone', 'images'])
                ->where('slug', $slug)
                ->firstOrFail();

            // Формирование массива изображений с полными данными для галереи
            $imagesArray = [];
            if ($product->images && $product->images->count() > 0) {
                foreach ($product->images as $img) {
                    $imagesArray[] = [
                        'id' => $img->id ?? null,
                        'path' => $img->path ?? '',
                        'url' => $img->path ? asset('storage/' . $img->path) : '',
                        'is_main' => (bool)($img->is_main ?? false),
                        'sort_order' => $img->sort_order ?? 0,
                        'alt_text' => $img->alt_text ?? $product->name,
                    ];
                }
            }

            // Обработка поля sizes: гарантируем корректный формат для фронтенда
            $sizesData = $product->sizes;
            
            if (is_string($sizesData)) {
                $sizesData = json_decode($sizesData, true);
            }
            
            if (!is_array($sizesData)) {
                $sizesData = [];
            }

            // Формирование ответа с приведением типов данных для совместимости с JS
            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description ?? '',
                'price' => (float)$product->price,
                'stock' => (int)$product->stock,
                'sizes' => $sizesData, 
                'image' => $product->image,
                'images' => $imagesArray,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug ?? null,
                ] : null,
                'material' => $product->material ? [
                    'id' => $product->material->id,
                    'name' => $product->material->name,
                ] : null,
                'stone' => $product->stone ? [
                    'id' => $product->stone->id,
                    'name' => $product->stone->name,
                    'color' => $product->stone->color ?? null,
                ] : null,
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Товар не найден: логируем и возвращаем 404
            Log::warning("Product not found by slug: {$slug}");
            return response()->json(['message' => 'Товар не найден'], 404);
            
        } catch (\Exception $e) {
            // Непредвиденная ошибка: логируем детали и возвращаем 500
            Log::error('Product show error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'message' => 'Ошибка сервера при загрузке товара',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}