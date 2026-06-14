<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер управления товарами в административной панели
 * Обрабатывает CRUD-операции, фильтрацию и загрузку изображений
 */
class ProductController extends Controller
{
    /**
     * Получение списка товаров с фильтрацией и пагинацией
     * Оптимизировано: предзагрузка изображений одним запросом (решение проблемы N+1)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Формирование базового запроса с джойнами к справочникам
            $query = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('materials', 'products.material_id', '=', 'materials.id')
                ->leftJoin('stones', 'products.stone_id', '=', 'stones.id')
                ->select(
                    'products.id', 'products.name', 'products.slug', 'products.price',
                    'products.stock', 'products.sizes', 'products.image', 'products.description',
                    'products.category_id', 'products.material_id', 'products.stone_id',
                    'products.created_at', 'products.updated_at',
                    'categories.name as category_name', 'materials.name as material_name',
                    'stones.name as stone_name', 'stones.color as stone_color'
                );

            // Применение фильтров из запроса
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'LIKE', "%{$search}%")
                      ->orWhere('products.description', 'LIKE', "%{$search}%");
                });
            }
            if ($request->filled('category_id')) {
                $query->where('products.category_id', $request->category_id);
            }
            if ($request->filled('material_id')) {
                $query->where('products.material_id', $request->material_id);
            }
            if ($request->filled('stone_id')) {
                $query->where('products.stone_id', $request->stone_id);
            }
            if ($request->filled('stock')) {
                if ($request->stock === 'in_stock') {
                    $query->where(function ($q) {
                        $q->where('products.stock', '>', 0)
                          ->orWhereRaw('JSON_LENGTH(products.sizes) > 0');
                    });
                } elseif ($request->stock === 'out_of_stock') {
                    $query->where('products.stock', 0)
                          ->whereRaw('(JSON_LENGTH(products.sizes) = 0 OR products.sizes IS NULL)');
                }
            }
            if ($request->filled('price_min')) {
                $query->where('products.price', '>=', $request->price_min);
            }
            if ($request->filled('price_max')) {
                $query->where('products.price', '<=', $request->price_max);
            }

            // Применение сортировки с валидацией полей
            $sortBy = $request->get('sort_by', 'created_desc');
            $parts = explode('_', $sortBy);
            $field = $parts[0];
            $direction = $parts[1] ?? 'desc';
            $validFields = ['name', 'price', 'stock', 'created'];
            $validDirections = ['asc', 'desc'];
            
            if (in_array($field, $validFields) && in_array($direction, $validDirections)) {
                $dbField = $field === 'created' ? 'products.created_at' : 'products.' . $field;
                $query->orderBy($dbField, $direction);
            } else {
                $query->orderBy('products.created_at', 'desc');
            }

            // Получение пагинированных результатов
            $products = $query->paginate($request->get('per_page', 50));

            // Предзагрузка главных изображений для всех товаров на странице (один запрос)
            $productIds = $products->pluck('id')->toArray();
            $mainImages = DB::table('product_images')
                ->whereIn('product_id', $productIds)
                ->where('is_main', true)
                ->get()
                ->keyBy('product_id');

            // Трансформация коллекции: декодирование JSON, формирование связанных объектов
            $products->getCollection()->transform(function ($product) use ($mainImages) {
                $product->sizes = $product->sizes ? json_decode($product->sizes, true) : [];
                
                $product->stone = $product->stone_id ? [
                    'id' => $product->stone_id, 'name' => $product->stone_name, 'color' => $product->stone_color
                ] : null;
                
                $product->material = $product->material_id ? [
                    'id' => $product->material_id, 'name' => $product->material_name
                ] : null;

                // Получение URL главного изображения из предзагруженной коллекции
                $mainImage = $mainImages->get($product->id);
                if ($mainImage && $mainImage->path) {
                    $product->image_url = asset('storage/' . $mainImage->path);
                } elseif ($product->image) {
                    $product->image_url = asset('storage/' . $product->image);
                } else {
                    $product->image_url = null;
                }

                // Удаление технических полей из ответа
                unset($product->stone_id, $product->stone_name, $product->stone_color, 
                      $product->material_id, $product->material_name);
                
                return $product;
            });

            return response()->json($products);
            
        } catch (\Exception $e) {
            Log::error('Admin Products index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки товаров: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение детальной информации о товаре по ID
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $product = DB::table('products')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('materials', 'products.material_id', '=', 'materials.id')
                ->leftJoin('stones', 'products.stone_id', '=', 'stones.id')
                ->select(
                    'products.*', 'categories.name as category_name',
                    'materials.name as material_name', 'stones.name as stone_name',
                    'stones.color as stone_color'
                )
                ->where('products.id', $id)
                ->first();

            if (!$product) {
                return response()->json(['message' => 'Товар не найден'], 404);
            }

            $product->sizes = $product->sizes ? json_decode($product->sizes, true) : [];
            
            $product->category = $product->category_id ? [
                'id' => $product->category_id, 'name' => $product->category_name
            ] : null;
            $product->material = $product->material_id ? [
                'id' => $product->material_id, 'name' => $product->material_name
            ] : null;
            $product->stone = $product->stone_id ? [
                'id' => $product->stone_id, 'name' => $product->stone_name, 'color' => $product->stone_color
            ] : null;

            // Загрузка всех изображений товара
            $product->images = DB::table('product_images')
                ->where('product_id', $id)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($img) {
                    return [
                        'id' => $img->id, 'path' => $img->path,
                        'url' => asset('storage/' . $img->path),
                        'is_main' => (bool)$img->is_main,
                        'sort_order' => $img->sort_order,
                        'alt_text' => $img->alt_text,
                    ];
                });

            $mainImage = collect($product->images)->firstWhere('is_main', true);
            $product->image_url = $mainImage?->url ?? ($product->image ? asset('storage/' . $product->image) : null);

            unset($product->category_name, $product->material_name, $product->stone_name, $product->stone_color);

            return response()->json($product);
        } catch (\Exception $e) {
            Log::error('Admin Product show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки товара: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение списка камней для выпадающего списка
     */
    public function getStones()
    {
        try {
            $stones = DB::table('stones')
                ->select('id', 'name', 'color')
                ->orderBy('name')
                ->get();
            return response()->json($stones);
        } catch (\Exception $e) {
            Log::error('Admin Stones error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки камней'], 500);
        }
    }

    /**
     * Получение списка материалов для выпадающего списка
     */
    public function getMaterials()
    {
        try {
            $materials = DB::table('materials')
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
            return response()->json($materials);
        } catch (\Exception $e) {
            Log::error('Admin Materials error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки материалов'], 500);
        }
    }

    /**
     * Создание нового товара
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Валидация входящих данных
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'category_id' => 'required|exists:categories,id',
                'material_id' => 'nullable|exists:materials,id',
                'stone_id' => 'nullable|exists:stones,id',
                'description' => 'nullable|string|max:65535',
                'stock' => 'nullable|integer|min:0',
                'sizes' => 'nullable|string|json',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'images' => 'nullable|array|max:4',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ], [
                'sizes.json' => 'Поле "Размеры" должно быть в формате JSON',
                'images.max' => 'Можно загрузить не более 4 изображений',
                'images.*.image' => 'Все файлы должны быть изображениями',
                'images.*.mimes' => 'Разрешены форматы: JPEG, PNG, JPG, GIF, WebP',
                'images.*.max' => 'Размер каждого файла не должен превышать 5MB',
            ]);

            // Генерация уникального slug
            $slug = $this->generateUniqueSlug($validated['name']);

            // Обработка логики остатков: для колец — сумма размеров, для остальных — прямое значение
            $sizesJson = null;
            $stockValue = 0;
            if (!empty($validated['sizes'])) {
                $sizesArray = is_string($validated['sizes']) 
                    ? json_decode($validated['sizes'], true) : $validated['sizes'];
                if (is_array($sizesArray) && !empty($sizesArray)) {
                    $sizesJson = is_string($validated['sizes']) 
                        ? $validated['sizes'] : json_encode($sizesArray, JSON_UNESCAPED_UNICODE);
                    $stockValue = array_sum(array_values($sizesArray));
                }
            }
            if ($sizesJson === null) {
                $stockValue = $validated['stock'] ?? 0;
            }

            // Загрузка основного изображения
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('products', 'public');
            }

            // Создание записи товара в БД
            $productId = DB::table('products')->insertGetId([
                'name' => $validated['name'], 'slug' => $slug, 'price' => $validated['price'],
                'category_id' => $validated['category_id'], 'material_id' => $validated['material_id'] ?? null,
                'stone_id' => $validated['stone_id'] ?? null, 'description' => $validated['description'] ?? null,
                'stock' => $stockValue, 'sizes' => $sizesJson, 'image' => $imagePath,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Сохранение дополнительных изображений
            $this->saveProductImages($productId, $request, $validated['category_id'] ?? null);

            return response()->json([
                'message' => 'Товар успешно создан', 'product_id' => $productId
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации', 'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка создания: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление существующего товара
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $exists = DB::table('products')->where('id', $id)->first();
            if (!$exists) {
                return response()->json(['message' => 'Товар не найден'], 404);
            }

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric|min:0',
                'category_id' => 'sometimes|required|exists:categories,id',
                'material_id' => 'nullable|exists:materials,id',
                'stone_id' => 'nullable|exists:stones,id',
                'description' => 'nullable|string|max:65535',
                'stock' => 'nullable|integer|min:0',
                'sizes' => 'nullable|string|json',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'images' => 'nullable|array|max:4',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ], [
                'sizes.json' => 'Поле "Размеры" должно быть в формате JSON',
                'images.max' => 'Можно загрузить не более 4 изображений',
                'images.*.image' => 'Все файлы должны быть изображениями',
                'images.*.mimes' => 'Разрешены форматы: JPEG, PNG, JPG, GIF, WebP',
                'images.*.max' => 'Размер каждого файла не должен превышать 5MB',
            ]);

            $updateData = [];

            // Обновление slug при изменении названия
            if (isset($validated['name'])) {
                $updateData['slug'] = $this->generateUniqueSlug($validated['name'], $id);
                $updateData['name'] = $validated['name'];
            }

            // Замена основного изображения при загрузке нового
            if ($request->hasFile('image')) {
                if ($exists->image && Storage::disk('public')->exists($exists->image)) {
                    Storage::disk('public')->delete($exists->image);
                }
                $updateData['image'] = $request->file('image')->store('products', 'public');
            }

            // Логика обновления остатков: пересчёт для колец или прямое значение для остальных
            $sizesJson = null;
            $stockValue = null;
            if (isset($validated['sizes'])) {
                $sizesArray = is_string($validated['sizes']) 
                    ? json_decode($validated['sizes'], true) : $validated['sizes'];
                if (is_array($sizesArray) && !empty($sizesArray)) {
                    $sizesJson = is_string($validated['sizes']) 
                        ? $validated['sizes'] : json_encode($sizesArray, JSON_UNESCAPED_UNICODE);
                    $stockValue = array_sum(array_values($sizesArray));
                } else {
                    $stockValue = $validated['stock'] ?? 0;
                    $sizesJson = null;
                }
            } elseif (isset($validated['stock'])) {
                $stockValue = $validated['stock'];
            }

            if ($stockValue !== null) {
                $updateData['stock'] = $stockValue;
            }
            if ($sizesJson !== null || isset($validated['sizes'])) {
                $updateData['sizes'] = $sizesJson;
            }

            // Обновление остальных полей
            $fields = ['price', 'category_id', 'material_id', 'stone_id', 'description'];
            foreach ($fields as $field) {
                if (isset($validated[$field])) {
                    $updateData[$field] = $validated[$field];
                }
            }
            $updateData['updated_at'] = now();

            DB::table('products')->where('id', $id)->update($updateData);
            $this->saveProductImages($id, $request, $validated['category_id'] ?? null);

            return response()->json(['message' => 'Товар успешно обновлён']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации', 'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Product update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка обновления: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление товара и связанных изображений
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $product = DB::table('products')->where('id', $id)->first();
            if (!$product) {
                return response()->json(['message' => 'Товар не найден'], 404);
            }

            // Удаление связанных изображений с диска и из БД
            $images = ProductImage::where('product_id', $id)->get();
            foreach ($images as $image) {
                if ($image->path && Storage::disk('public')->exists($image->path)) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            // Удаление основного изображения
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Удаление записи товара
            DB::table('products')->where('id', $id)->delete();

            return response()->json(['message' => 'Товар успешно удалён']);
        } catch (\Exception $e) {
            Log::error('Product destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка удаления: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Генерация уникального slug с обработкой дубликатов
     * 
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    private function generateUniqueSlug($name, $excludeId = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = DB::table('products')->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter++;
            $query = DB::table('products')->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    /**
     * Сохранение множественных изображений товара в папку по категории
     * 
     * @param int $productId
     * @param Request $request
     * @param int|null $categoryId
     */
    private function saveProductImages($productId, Request $request, $categoryId = null)
    {
        if (!$request->hasFile('images')) {
            return;
        }

        // Определение папки на основе slug категории
        $categorySlug = 'other';
        if ($categoryId) {
            $category = DB::table('categories')->find($categoryId);
            if ($category && !empty($category->slug)) {
                $categorySlug = Str::slug($category->slug);
            }
        }

        $directory = 'products/' . $categorySlug;
        $files = $request->file('images');
        $filesToProcess = array_slice($files, 0, 4);

        $startOrder = ProductImage::where('product_id', $productId)->max('sort_order') ?? -1;
        $productName = DB::table('products')->value('name', $productId) ?? 'Товар';

        foreach ($filesToProcess as $index => $file) {
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '_' . ($startOrder + $index + 1) . '.' . $extension;
            $path = $file->storeAs($directory, $filename, 'public');
            
            if ($path) {
                ProductImage::create([
                    'product_id' => $productId, 'path' => $path,
                    'sort_order' => $startOrder + $index + 1,
                    'is_main' => ProductImage::where('product_id', $productId)->count() === 0,
                    'alt_text' => $productName,
                ]);
            }
        }
    }

    /**
     * Удаление изображения товара по ID
     * 
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyImage($imageId)
    {
        try {
            $image = ProductImage::find($imageId);
            if (!$image) {
                return response()->json(['message' => 'Изображение не найдено'], 404);
            }

            if ($image->path && Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }
            $image->delete();

            return response()->json(['message' => 'Изображение успешно удалено']);
        } catch (\Exception $e) {
            Log::error('Image destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка удаления изображения: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Установка изображения как главного для товара
     * 
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function setMainImage($imageId)
    {
        try {
            $image = ProductImage::find($imageId);
            if (!$image) {
                return response()->json(['message' => 'Изображение не найдено'], 404);
            }

            // Сброс флага is_main у всех изображений товара
            ProductImage::where('product_id', $image->product_id)
                ->update(['is_main' => false]);
            // Установка флага для выбранного изображения
            $image->update(['is_main' => true]);

            return response()->json(['message' => 'Главное изображение установлено']);
        } catch (\Exception $e) {
            Log::error('Set main image error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получение списка всех изображений товара
     * 
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages($productId)
    {
        try {
            $product = DB::table('products')->where('id', $productId)->first();
            if (!$product) {
                return response()->json(['message' => 'Товар не найден'], 404);
            }

            $images = ProductImage::where('product_id', $productId)
                ->orderBy('sort_order')
                ->get()
                ->map(function ($img) {
                    return [
                        'id' => $img->id, 'path' => $img->path,
                        'url' => asset('storage/' . $img->path),
                        'is_main' => (bool)$img->is_main,
                        'sort_order' => $img->sort_order,
                        'alt_text' => $img->alt_text,
                    ];
                });

            return response()->json(['success' => true, 'images' => $images]);
        } catch (\Exception $e) {
            Log::error('Get images error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки изображений: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Обновление порядка изображений (drag-and-drop)
     * 
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderImages(Request $request, $productId)
    {
        try {
            $product = DB::table('products')->where('id', $productId)->first();
            if (!$product) {
                return response()->json(['message' => 'Товар не найден'], 404);
            }

            $orderData = $request->validate([
                'order' => 'required|array',
                'order.*.id' => 'required|integer|exists:product_images,id',
                'order.*.sort_order' => 'required|integer|min:0',
            ]);

            foreach ($orderData['order'] as $item) {
                ProductImage::where('id', $item['id'])
                    ->where('product_id', $productId)
                    ->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json(['message' => 'Порядок изображений обновлён']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации', 'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Reorder images error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }
}