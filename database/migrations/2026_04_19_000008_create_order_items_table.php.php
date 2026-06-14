<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Запуск миграций
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            
            // === ВНЕШНИЕ КЛЮЧИ ===
            
            // Если удаляется ЗАКАЗ — удаляем и его позиции (логично)
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();
            
            // Если удаляется ТОВАР — позиция заказа СОХРАНЯЕТСЯ (история)
            // Поэтому НЕТ cascadeOnDelete, а просто ссылка
            $table->foreignId('product_id')
                  ->constrained('products');
            
            // === ДАННЫЕ ПОЗИЦИИ ===
            
            $table->integer('quantity'); // Количество
            
            //  фиксируем цену на момент покупки
            // Если цена товара изменится, старые заказы не пострадают
            $table->decimal('price', 10, 2);
            
            // Размер (для колец)
            $table->string('size', 10)->nullable();
            
            // сохраняем название и картинку
            // Чтобы история заказов не зависела от изменений в каталоге
            $table->string('product_name', 150);
            $table->string('product_image', 255)->nullable();
            
            // Системные поля
            $table->timestamps();
            
            // === ИНДЕКСЫ ===
            // составной индекс для аналитики:
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Откат миграций
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};