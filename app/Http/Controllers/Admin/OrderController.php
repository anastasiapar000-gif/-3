<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Получение списка всех заказов с пагинацией
     * Возвращает заказы с данными клиента и информацией о доставке
     * 
     */
    public function index()
    {
        try {
            // Загружаем заказы с присоединёнными данными пользователя
            // COALESCE выбирает первое непустое значение для имени клиента
            $orders = DB::table('orders')
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'orders.*',
                    DB::raw('COALESCE(users.full_name, users.email, "Гость") as customer_name'),
                    DB::raw('COALESCE(users.email, "Нет email") as customer_email'),
                    'orders.delivery_method',
                    'orders.delivery_price',
                    'orders.cdek_city',
                    'orders.address'
                )
                ->orderBy('orders.created_at', 'desc')
                ->paginate(20);

            return response()->json($orders);

        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем ответ с кодом 500
            \Log::error('Admin Orders index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка: ' . $e->getMessage(),
                'orders' => []
            ], 500);
        }
    }

    /**
     * Получение детальной информации о заказе
     * Возвращает заказ, товары и форматированную информацию о доставке
     * 
     */
    public function show($id)
    {
        try {
            // Получаем заказ с данными клиента
            $order = DB::table('orders')
                ->where('orders.id', $id)
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->select(
                    'orders.*',
                    DB::raw('COALESCE(users.full_name, users.email, "Гость") as customer_name'),
                    'users.email as user_email',
                    'users.phone as user_phone'
                )
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Заказ не найден'], 404);
            }

            // Получаем товары заказа с данными продуктов и камней
            $items = DB::table('order_items')
                ->where('order_items.order_id', $id)
                ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
                ->leftJoin('stones', 'products.stone_id', '=', 'stones.id')
                ->select(
                    'order_items.id',
                    'order_items.order_id',
                    'order_items.product_id',
                    'order_items.quantity',
                    'order_items.price',
                    'order_items.size',
                    'products.name as product_name',
                    'products.slug as product_slug',
                    'products.image as product_image',
                    'stones.name as stone_name',
                    'stones.color as stone_color'
                )
                ->get();

            // Форматируем товары для ответа фронтенду
            $formattedItems = $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => (int)$item->quantity,
                    'price' => (float)$item->price,
                    'size' => $item->size,
                    'product_name' => $item->product_name ?? 'Товар удалён',
                    'product_slug' => $item->product_slug ?? '',
                    'product_image' => $item->product_image ?? '',
                    'stone' => $item->stone_name ? [
                        'name' => $item->stone_name,
                        'color' => $item->stone_color
                    ] : null,
                ];
            });

            // Форматируем информацию о доставке
            $deliveryInfo = $this->formatDeliveryInfo($order);

            return response()->json([
                'order' => $order,
                'delivery' => $deliveryInfo,
                'items' => $formattedItems
            ]);

        } catch (\Exception $e) {
            \Log::error('Admin order show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Форматирование информации о доставке для отображения на фронтенде
     * 
    
     */
    private function formatDeliveryInfo($order)
    {
        $delivery = [
            'method' => $order->delivery_method,
            'method_name' => $this->getDeliveryMethodName($order->delivery_method),
            'price' => (float)($order->delivery_price ?? 0),
            'price_formatted' => $order->delivery_price == 0 ? 'Бесплатно' : number_format($order->delivery_price, 0, '.', ' ') . ' ₽',
        ];

        // Добавляем детали доставки в зависимости от выбранного метода
        switch ($order->delivery_method) {
            case 'cdek':
                $delivery['details'] = [
                    'city' => $order->cdek_city,
                    'address' => $order->cdek_address,
                    'note' => 'Адрес пункта выдачи укажем в подтверждении',
                ];
                break;
                
            case 'irkutsk':
                $delivery['details'] = [
                    'address' => $order->address,
                    'entrance' => $order->entrance,
                    'floor' => $order->floor,
                    'intercom' => $order->intercom,
                ];
                break;
                
            case 'pickup':
                $delivery['details'] = [
                    'address' => $order->pickup_address ?? 'г. Иркутск, ул. Ленина, 5А',
                    'schedule' => 'Пн-Пт 10:00–19:00, Сб 11:00–17:00',
                    'note' => 'Готовность заказа: 10-14 рабочих дней',
                ];
                break;
        }

        return $delivery;
    }

    /**
     * Возвращает читаемое название способа доставки по ключу
     * 
     * @param string $method
     * @return string
     */
    private function getDeliveryMethodName($method)
    {
        $names = [
            'pickup' => 'Самовывоз',
            'irkutsk' => 'Доставка по Иркутску',
            'cdek' => 'СДЭК',
        ];
        return $names[$method] ?? $method;
    }

    /**
     * Обновление статуса заказа
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Валидация нового статуса
            $request->validate([
                'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,completed',
            ]);

            // Обновляем статус заказа в базе данных
            $updated = DB::table('orders')
                ->where('id', $id)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now(),
                ]);

            if (!$updated) {
                return response()->json(['message' => 'Заказ не найден'], 404);
            }

            return response()->json([
                'message' => 'Статус обновлён',
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            \Log::error('Update status error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление заказа и связанных товаров
    
     */
    public function destroy($id)
    {
        try {
            // Удаляем связанные товары заказа (внешний ключ)
            DB::table('order_items')->where('order_id', $id)->delete();
            
            // Удаляем сам заказ
            $deleted = DB::table('orders')->where('id', $id)->delete();

            if (!$deleted) {
                return response()->json(['message' => 'Заказ не найден'], 404);
            }

            return response()->json(['message' => 'Заказ удалён']);

        } catch (\Exception $e) {
            \Log::error('Delete order error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка: ' . $e->getMessage()
            ], 500);
        }
    }
}