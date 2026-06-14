<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Получение сводной статистики для дашборда
     * 
     */
    public function stats()
    {
        try {
            // Подсчёт общего количества товаров, заказов и пользователей
            $productsCount = Product::count();
            $ordersCount = Order::count();
            $usersCount = User::count();
            
            // Расчёт выручки только по завершённым (доставленным) заказам
            $revenue = Order::where('status', 'delivered')->sum('total_amount');

            return response()->json([
                'products' => (int) $productsCount,
                'orders' => (int) $ordersCount,
                'users' => (int) $usersCount,
                'revenue' => (float) $revenue,
            ]);
        } catch (\Exception $e) {
            // Логирование ошибки и возврат нулевых значений при сбое
            \Log::error('Dashboard stats error: ' . $e->getMessage());
            return response()->json([
                'products' => 0,
                'orders' => 0,
                'users' => 0,
                'revenue' => 0,
            ]);
        }
    }

    /**
     * Получение списка последних 5 заказов для отображения на дашборде
     */
    public function recentOrders()
    {
        try {
            // Загрузка заказов с подгруженными данными пользователя (только необходимые поля)
            $orders = Order::with('user:id,full_name,email')
                ->latest('created_at')
                ->take(5)
                ->get()
                ->map(function ($order) {
                    // Формирование упрощённого ответа для фронтенда
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'total' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at,
                        'customer_name' => $order->user->full_name ?? $order->user->email ?? 'Гость',
                    ];
                });

            return response()->json($orders);
        } catch (\Exception $e) {
            // Возврат пустого массива при ошибке
            \Log::error('Dashboard recentOrders error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Базовый эндпоинт проверки доступности админ-панели
     * 
     */
    public function index()
    {
        return response()->json([
            'message' => 'Добро пожаловать в админ-панель ZIMA',
            'timestamp' => now(),
        ]);
    }
}