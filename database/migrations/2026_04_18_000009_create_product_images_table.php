<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path'); // Путь к файлу изображения
            $table->integer('sort_order')->default(0); // Порядок сортировки
            $table->boolean('is_main')->default(false); // Главное фото
            $table->string('alt_text')->nullable(); 
            $table->timestamps();
            
            // Индекс для быстрого поиска
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};