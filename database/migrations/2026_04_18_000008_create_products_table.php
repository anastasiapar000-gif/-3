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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Внешние ключи
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stone_id')->nullable()->constrained()->nullOnDelete();
            
            // Основные поля товара
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Цена и остатки
            $table->decimal('price', 10, 2);
            
            // Для колец: объект с количеством по каждому размеру
            // Для остальных товаров: null
            $table->json('sizes')->nullable();
            
            $table->integer('stock')->default(0);
            $table->string('image')->nullable();
            
            // Системные поля
            $table->timestamps();
        });
    }

    /**
     * Откат миграций
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};