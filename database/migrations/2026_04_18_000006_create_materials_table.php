<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            
            // Название материала (Серебро 925, Золото 585 и т.д.)
            $table->string('name', 100)->unique();
            
            // Описание или проба (текст, а не строка)
            $table->text('description')->nullable();
            
            // Slug для ЧПУ-ссылок (обязательный, уникальный)
            $table->string('slug', 100)->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};