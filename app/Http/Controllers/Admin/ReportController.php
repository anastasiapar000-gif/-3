<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class ReportController extends Controller
{
    /**
     * Экспорт отчёта по заказам в Excel
     * 
     * @return \Maatwebsite\Excel\BinaryFile|JsonResponse
     */
    public function exportOrdersExcel()
    {
        try {
            // Генерация и скачивание файла с отчётом
            return Excel::download(new OrdersExport, 'orders_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            // Логирование ошибки и возврат ответа с кодом 500
            Log::error('Excel Orders Export error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка при формировании Excel-отчёта: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Экспорт каталога товаров в Excel
     * 
     */
    public function exportProductsExcel()
    {
        try {
            // Генерация и скачивание файла с каталогом
            return Excel::download(new ProductsExport, 'products_catalog_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Excel Products Export error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка при формировании Excel-отчёта: ' . $e->getMessage()], 500);
        }
    }
}

/**
 * Класс экспорта заказов для Laravel Excel
 * Реализует интерфейсы для формирования данных, заголовков и строк
 */
class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Получение данных заказов из базы данных
     * 
     */
    public function collection()
    {
        return DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.order_number',
                'orders.created_at',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'orders.total_amount',
                'orders.status',
                'orders.delivery_method',
                'orders.address'
            )
            ->orderBy('orders.created_at', 'desc')
            ->get();
    }

    /**
     * Заголовки столбцов в экспортируемом файле
     * 
     */
    public function headings(): array
    {
        return [
            'Номер заказа',
            'Дата оформления',
            'Клиент',
            'Email',
            'Сумма (₽)',
            'Статус',
            'Способ доставки',
            'Адрес доставки'
        ];
    }

    /**
     * Преобразование строки данных для экспорта
     * Форматирует дату, сумму, статус и способ доставки
     * 
     * 
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            date('d.m.Y H:i', strtotime($order->created_at)),
            $order->customer_name ?? 'Гость',
            $order->customer_email ?? '-',
            number_format($order->total_amount, 2, '.', ' '),
            $this->getStatusText($order->status),
            $this->getDeliveryText($order->delivery_method),
            $order->address ?? '-'
        ];
    }

    /**
     * Преобразование ключа статуса в читаемое название
     * 
     *
     */
    private function getStatusText($status): string
    {
        $statuses = [
            'pending' => 'Новый',
            'confirmed' => 'Подтверждён',
            'processing' => 'В обработке',
            'shipped' => 'Отправлен',
            'delivered' => 'Доставлен',
            'cancelled' => 'Отменён'
        ];
        return $statuses[$status] ?? $status;
    }

    /**
     * Преобразование ключа способа доставки в читаемое название
     * 
     *
     */
    private function getDeliveryText($method): string
    {
        $methods = [
            'pickup' => 'Самовывоз',
            'irkutsk' => 'Доставка по Иркутску',
            'cdek' => 'СДЭК'
        ];
        return $methods[$method] ?? $method;
    }
}

/**
 * Класс экспорта товаров для Laravel Excel
 * Реализует интерфейсы для формирования данных, заголовков и маппинга строк
 */
class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Получение данных товаров с подгруженными связями
     * 
     */
    public function collection()
    {
        return Product::with(['category', 'material', 'stone'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Заголовки столбцов в экспортируемом файле
     * 
     */
    public function headings(): array
    {
        return [
            'ID',
            'Название',
            'Категория',
            'Материал',
            'Камень',
            'Цена (₽)',
            'Остаток',
            'Описание',
            'Дата создания'
        ];
    }

    /**
     * Преобразование модели товара в массив для экспорта
     * Форматирует цену и дату, обрабатывает отсутствующие связи
     * 
     
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->category?->name ?? '—',
            $product->material?->name ?? '—',
            $product->stone?->name ?? '—',
            number_format($product->price, 2, '.', ' '),
            $product->stock,
            $product->description,
            $product->created_at?->format('d.m.Y H:i')
        ];
    }
}