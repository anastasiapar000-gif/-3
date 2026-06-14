<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            
            // ИСПРАВЛЕНО: ссылаться на 'id', а не 'user_id'
            $table->foreignId('user_id')
                  ->constrained('users')  
                  ->cascadeOnDelete();
            
            // Адресные поля
            $table->string('city', 100);
            $table->string('street', 255);
            $table->string('building', 20);
            $table->string('apartment', 20)->nullable();
            $table->string('zip_code', 10)->nullable();
            
            // Контактные данные
            $table->string('phone', 20)->nullable();
            $table->text('delivery_comment')->nullable();
            
            // Флаг адреса по умолчанию
            $table->boolean('is_default')->default(false);
            
            // Мягкое удаление
            $table->softDeletes();
            
            // Временные метки
            $table->timestamps();
            
            // Индексы
            $table->index(['user_id', 'is_default']);
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};