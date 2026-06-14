<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            
            // Имя отправителя 
            $table->string('name', 100);
            
            // Контакт: email или телефон
            $table->string('contact', 100)->index(); // ← Индекс для быстрого поиска
            
            // Сообщение
            $table->text('message');
            
            // Статус прочтения
            $table->boolean('is_read')->default(false)->index(); // ← Индекс для фильтрации "непрочитанные"
            

            $table->string('subject', 150)->nullable(); // Тема сообщения
            $table->string('ip_address', 45)->nullable(); // до 45 символов (защита от спама)
            $table->softDeletes(); // Мягкое удаление (чтобы не терять историю)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};