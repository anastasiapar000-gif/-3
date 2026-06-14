<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    /**
     * Оформление нового заказа
     * Проверяет наличие товаров, списывает остатки, создаёт заказ и позиции
     * 
     * 
     */
    public function checkout(Request $request)
    {
        // Валидация входящих данных заказа
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id', 
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.size' => 'nullable|string|max:10',
            
            'delivery_method' => 'required|in:pickup,irkutsk,cdek',
            'delivery_price' => 'required|numeric|min:0',
            
            'cdek_city' => 'required_if:delivery_method,cdek|string|max:100',
            'cdek_address' => 'required_if:delivery_method,cdek|string|max:255',
            'address' => 'required_if:delivery_method,irkutsk|string|max:255',
            'entrance' => 'nullable|string|max:10',
            'floor' => 'nullable|string|max:10',
            'intercom' => 'nullable|string|max:20',
            'pickup_address' => 'nullable|string|max:255',
            
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:card,cash,sbp',
            'comment' => 'nullable|string|max:1000',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ], [
            'items.*.product_id.exists' => 'Один из товаров был удален из каталога. Обновите корзину.',
        ]);

        // Проверка авторизации пользователя
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Не авторизован'], 401);

        // Начало транзакции для атомарности операций
        DB::beginTransaction();
        try {
            $total = $validated['delivery_price'];
            $orderItemsData = [];

            // Проверка наличия товаров и расчёт итоговой суммы
            foreach ($validated['items'] as $item) {
                // Блокировка строки товара для предотвращения гонки данных
                $product = Product::lockForUpdate()->find($item['product_id']);
                
                if (!$product) {
                    throw new \Exception("Товар ID {$item['product_id']} не найден.");
                }

                $size = $item['size'] ?? null;
                $quantity = $item['quantity'];

                // Проверка остатков с учётом размеров (JSON-поле sizes)
                if ($product->sizes && is_array($product->sizes)) {
                    if (!$size) {
                         throw new \Exception("Для товара '{$product->name}' необходимо выбрать размер.");
                    }
                    if (!isset($product->sizes[$size]) || $product->sizes[$size] < $quantity) {
                        throw new \Exception("Недостаточно товара '{$product->name}' размера {$size}. Доступно: " . ($product->sizes[$size] ?? 0));
                    }
                } else {
                    // Проверка остатков для товаров без размеров
                    if ($product->stock < $quantity) {
                        throw new \Exception("Недостаточно товара '{$product->name}'. Доступно: {$product->stock}");
                    }
                }

                $currentPrice = $item['price']; 
                $total += $currentPrice * $quantity;

                // Подготовка данных для создания позиции заказа
                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $currentPrice,
                    'size' => $size,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                ];
            }

            // Создание записи заказа в базе данных
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
                'total_amount' => $total,
                'status' => 'pending',
                'delivery_method' => $validated['delivery_method'],
                'delivery_price' => $validated['delivery_price'],
                'cdek_city' => $validated['cdek_city'] ?? null,
                'cdek_address' => $validated['cdek_address'] ?? null,
                'address' => $validated['address'] ?? null,
                'entrance' => $validated['entrance'] ?? null,
                'floor' => $validated['floor'] ?? null,
                'intercom' => $validated['intercom'] ?? null,
                'pickup_address' => $validated['pickup_address'] ?? null,
                'phone' => $validated['phone'],
                'payment_method' => $validated['payment_method'],
                'comment' => $validated['comment'] ?? null,
                'customer_name' => $validated['name'],
                'customer_email' => $validated['email'],
            ]);

            // Создание позиций заказа и списание остатков товаров
            foreach ($orderItemsData as $itemData) {
                OrderItem::create([
                    'order_id' => $order->id,
                    ...$itemData
                ]);

                $product = Product::find($itemData['product_id']);
                
                if ($product) {
                    // Списываем остатки с учётом размеров
                    if ($product->sizes && is_array($product->sizes) && $itemData['size']) {
                        $currentSizes = $product->sizes;
                        $currentSizes[$itemData['size']] -= $itemData['quantity'];
                        
                        if ($currentSizes[$itemData['size']] <= 0) {
                            unset($currentSizes[$itemData['size']]);
                        }
                        
                        $product->sizes = $currentSizes;
                        $product->stock = array_sum($currentSizes);
                    } else {
                        // Списываем остатки для товаров без размеров
                        $product->stock -= $itemData['quantity'];
                    }
                    
                    $product->save();
                }
            }

            // Фиксация транзакции
            DB::commit();
            return response()->json(['message' => 'Заказ оформлен', 'order' => ['order_number' => $order->order_number]], 201);

        } catch (\Exception $e) {
            // Откат транзакции при ошибке
            DB::rollBack();
            \Log::error('Checkout error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Получение списка заказов текущего пользователя
     * 
     */
    public function userOrders(Request $request)
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($order) => $this->formatOrderResponse($order));
        return response()->json($orders);
    }

    /**
     * Получение детальной информации о заказе пользователя
     * 
     * 
     */
    public function showUserOrder(Request $request, $id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)
            ->where('id', $id)
            ->with('items')
            ->firstOrFail();
        return response()->json($this->formatOrderResponse($order, true));
    }

    /**
     * Отмена заказа пользователем с возвратом остатков товаров
     * 
     *
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        $order = Order::where('user_id', $user->id)->where('id', $id)->with('items')->firstOrFail();

        // Проверка возможности отмены заказа по статусу
        if (!in_array($order->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'Нельзя отменить заказ в статусе: ' . $order->status], 422);
        }

        DB::beginTransaction();
        try {
            // Возврат остатков товаров на склад
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) continue;

                if ($product->sizes && is_array($product->sizes) && $item->size) {
                    $currentSizes = $product->sizes;
                    $currentSizes[$item->size] = ($currentSizes[$item->size] ?? 0) + $item->quantity;
                    
                    $product->sizes = $currentSizes;
                    $product->stock = array_sum($currentSizes);
                } else {
                    $product->stock += $item->quantity;
                }
                
                $product->save();
            }
            
            // Обновление статуса заказа
            $order->update(['status' => 'cancelled', 'cancelled_at' => now()]);
            DB::commit();
            return response()->json(['message' => 'Заказ отменён', 'order' => $order]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Ошибка отмены: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Форматирование данных заказа для ответа клиенту
     * 
     */
    private function formatOrderResponse($order, $includeDetails = false)
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_amount' => $order->total_amount,
            'total' => $order->total_amount,
            'created_at' => $order->created_at,
            'delivery_method' => $order->delivery_method,
            'delivery_method_name' => $this->getDeliveryMethodName($order->delivery_method),
            'delivery_price' => $order->delivery_price,
            'delivery_price_formatted' => $order->delivery_price == 0 ? 'Бесплатно' : number_format($order->delivery_price, 0, '.', ' ') . ' ₽',
            'payment_method' => $order->payment_method,
            'payment_method_name' => $this->getPaymentMethodName($order->payment_method),
            'shipping_address_full' => $this->formatShippingAddress($order),
            'comment' => $order->comment,
            'items' => $order->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'size' => $item->size,
                    'total' => $item->price * $item->quantity,
                    'product_image' => $item->product_image,
                ];
            })->toArray(),
        ];
    }

    /**
     * Преобразование ключа способа доставки в читаемое название
     * 
     */
    private function getDeliveryMethodName($method) {
        return match($method) {
            'pickup' => 'Самовывоз',
            'irkutsk' => 'Доставка по Иркутску',
            'cdek' => 'СДЭК',
            default => $method,
        };
    }

    /**
     * Преобразование ключа способа оплаты в читаемое название
     * 
     
     */
    private function getPaymentMethodName($method) {
        return match($method) {
            'card' => 'Банковская карта',
            'cash' => 'Наличными',
            'sbp' => 'СБП',
            default => $method,
        };
    }

    /**
     * Форматирование адреса доставки для отображения
     * 
     *
     */
    private function formatShippingAddress($order): ?string {
        return match($order->delivery_method) {
            'cdek' => ($order->cdek_city ?? '') . ', ' . ($order->cdek_address ?? ''),
            'irkutsk' => implode(', ', array_filter([$order->address, $order->entrance ? "подъезд {$order->entrance}" : null])),
            'pickup' => $order->pickup_address ?? 'г. Иркутск, ул. Ленина, 5А',
            default => $order->address,
        };
    }
}