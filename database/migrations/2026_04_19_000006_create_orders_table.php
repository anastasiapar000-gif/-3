<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // Уникальный номер заказа 
            $table->string('order_number', 50)->unique();
            
            $table->decimal('total_amount', 10, 2);
            
            // Статус заказа
            $table->enum('status', [
                'pending',      // Ожидает оплаты
                'confirmed',    // Подтверждён
                'processing',   // В обработке
                'shipped',      // Отправлен
                'delivered',    // Доставлен
                'cancelled'     // Отменён
            ])->default('pending');
            
            // === ПОЛЯ ДОСТАВКИ ===
            $table->string('delivery_method', 20)->default('pickup');
            $table->decimal('delivery_price', 10, 2)->default(0);
            
            // Для СДЭК
            $table->string('cdek_city', 100)->nullable();
            $table->string('cdek_address', 255)->nullable();
            
            // Для Иркутска (курьер)
            $table->string('address', 255)->nullable();
            $table->string('entrance', 10)->nullable();
            $table->string('floor', 10)->nullable();
            $table->string('intercom', 20)->nullable();
            
            // Для самовывоза
            $table->string('pickup_address', 255)->nullable();
            
            // Контакты и оплата
            $table->string('phone', 20)->nullable();
            $table->enum('payment_method', ['card', 'cash', 'sbp'])->default('card');
            $table->text('comment')->nullable();
            
            $table->timestamps();
            
            // === ИНДЕКСЫ ===
            $table->index('delivery_method');  // Фильтрация по способу доставки
            $table->index('status');            // Фильтрация по статусу
            $table->index('user_id');           // ускорение поиска заказов пользователя
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};