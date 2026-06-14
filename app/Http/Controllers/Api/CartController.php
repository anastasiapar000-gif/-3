<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Получить корзину пользователя
     * GET /api/cart
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $cartItems = Cart::where('user_id', $user->id)
                ->with(['product', 'product.category', 'product.material', 'product.stone'])
                ->get()
                ->map(fn($item) => $this->formatCartItem($item));

            return response()->json($cartItems);
        } catch (\Exception $e) {
            \Log::error('Cart index error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки корзины'], 500);
        }
    }

    /**
     * Добавить товар в корзину
     * POST /api/cart/add
     */
    public function addToCart(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|integer|exists:products,id',
                'quantity'   => 'required|integer|min:1',
                'size'       => 'nullable|string|max:10',
            ]);

            $user = $request->user();
            $product = Product::findOrFail($validated['product_id']);
            $size = $validated['size'] ? (string) $validated['size'] : null;

            // Проверка наличия по размерам или общему остатку
            if ($product->hasSizes()) {
                if (!$size) {
                    return response()->json(['message' => 'Выберите размер'], 422);
                }
                if (!isset($product->sizes[$size]) || $product->sizes[$size] <= 0) {
                    return response()->json(['message' => 'Недопустимый размер или нет в наличии'], 422);
                }
                if ($product->sizes[$size] < $validated['quantity']) {
                    return response()->json(['message' => 'Недостаточно товара этого размера'], 422);
                }
            } else {
                if ($product->stock < $validated['quantity']) {
                    return response()->json(['message' => 'Недостаточно товара'], 422);
                }
            }

            DB::beginTransaction();
            
            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $validated['product_id'])
                ->where('size', $size)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $validated['quantity'];
                $cartItem->save();
            } else {
                Cart::create([
                    'user_id'    => $user->id,
                    'product_id' => $validated['product_id'],
                    'quantity'   => $validated['quantity'],
                    'size'       => $size,
                ]);
            }
            
            DB::commit();

            return response()->json([
                'message' => 'Товар добавлен в корзину',
                'cart'    => $this->getUserCart($user->id)
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка добавления в корзину'], 500);
        }
    }

    /**
     * Удалить товар из корзины
     * DELETE /api/cart/remove/{id}
     */
    public function removeFromCart(Request $request, $id)
    {
        try {
            $user = $request->user();
            $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            $cartItem->delete();

            return response()->json([
                'message' => 'Товар удалён из корзины',
                'cart'    => $this->getUserCart($user->id)
            ]);
        } catch (\Exception $e) {
            \Log::error('Remove from cart error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка удаления из корзины'], 500);
        }
    }

    /**
     * Обновить количество товара
     * PUT /api/cart/update/{id}
     */
    public function updateQuantity(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1'
            ]);

            $user = $request->user();
            $cartItem = Cart::where('id', $id)->where('user_id', $user->id)->firstOrFail();
            $product = $cartItem->product;
            $size = $cartItem->size;

            // Повторная проверка остатков при изменении количества
            if ($product->hasSizes() && $size && isset($product->sizes[$size])) {
                if ($product->sizes[$size] < $validated['quantity']) {
                    return response()->json(['message' => 'Недостаточно товара этого размера'], 422);
                }
            } elseif (!$product->hasSizes() && $product->stock < $validated['quantity']) {
                return response()->json(['message' => 'Недостаточно товара'], 422);
            }

            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();

            return response()->json([
                'message' => 'Количество обновлено',
                'cart'    => $this->getUserCart($user->id)
            ]);
        } catch (\Exception $e) {
            \Log::error('Update cart quantity error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка обновления количества'], 500);
        }
    }

    /**
     * Очистить корзину
     * DELETE /api/cart/clear
     */
    public function clear(Request $request)
    {
        try {
            Cart::where('user_id', $request->user()->id)->delete();
            return response()->json(['message' => 'Корзина очищена']);
        } catch (\Exception $e) {
            \Log::error('Clear cart error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка очистки корзины'], 500);
        }
    }

    
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ

    /**
     * Получить корзину пользователя с форматированием
     */
    private function getUserCart($userId)
    {
        return Cart::where('user_id', $userId)
            ->with(['product', 'product.category', 'product.material', 'product.stone'])
            ->get()
            ->map(fn($item) => $this->formatCartItem($item));
    }

    /**
     * Форматировать элемент корзины для ответа API
     */
   private function formatCartItem($item)
{
    $product = $item->product;
    
    //  Получаем первое изображение (приоритет: all_images, затем image)
    $imageUrl = null;
    if ($product) {
        $allImages = $product->all_images ?? [];
        if (!empty($allImages) && isset($allImages[0]['url'])) {
            $imageUrl = $allImages[0]['url'];
        } elseif ($product->image) {
            $imageUrl = asset('storage/' . $product->image);
        }
    }
    
    return [
        'id'         => $item->id,
        'product_id' => $item->product_id,
        'quantity'   => $item->quantity,
        'size'       => $item->size,
        
        // Поля товара на верхнем уровне
        'name'       => $product ? $product->name : 'Товар удален',
        'price'      => $product ? $product->price : 0,
        'image'      => $imageUrl, // 🔧 ИСПРАВЛЕНО: правильный URL изображения
        'images'     => $product ? ($product->all_images ?? []) : [],
        
        // Полный объект product для сложных проверок
        'product'    => $product ? [
            'id'       => $product->id,
            'name'     => $product->name,
            'price'    => $product->price,
            'image'    => $product->image,
            'images'   => $product->all_images ?? [],
            'stock'    => $product->stock,
            'sizes'    => $product->sizes,
            'category' => $product->category,
            'material' => $product->material,
            'stone'    => $product->stone,
        ] : null,
    ];
}
}