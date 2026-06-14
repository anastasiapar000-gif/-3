<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stones', function (Blueprint $table) {
            $table->id();
            
            // Название камня (Фианит, Горный хрусталь...) — уникальное
            $table->string('name', 100)->unique();
            
            // Цвет камня (Прозрачный, Голубой, Красный...)
            $table->string('color', 50)->nullable()->index();
            
            // Подробное описание
            $table->text('description')->nullable();
            
            // Slug для ЧПУ-ссылок (генерируется автоматически)
            $table->string('slug', 100)->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stones');
    }
};