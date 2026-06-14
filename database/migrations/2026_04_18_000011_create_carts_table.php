<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            
            // Связь с пользователем
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // Связь с товаром
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->cascadeOnDelete();
            
            // Количество единиц
            $table->integer('quantity')->default(1)->unsigned();
            
            // Размер (для колец) — явная длина для оптимизации
            $table->string('size', 10)->nullable();
            
            $table->timestamps();
            
            // Уникальный составной индекс: один товар + один размер = одна запись в корзине
            $table->unique(['user_id', 'product_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};