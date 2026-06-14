<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class CreateAdminCommand extends Command
{
   
    /**
     * Краткое описание команды, отображаемое в списке доступных команд
     */
    protected $description = 'Создать администратора';
protected $signature = 'make:admin {email?} {password?} {--name= : Full name}';
    /**
     * Выполнение команды
     * 
     * @return int Код завершения: 0 — успех, ненулевое значение — ошибка
     */
    public function handle()
    {
        // Получение email: из аргумента командной строки или запрос у пользователя
        $email = $this->argument('email') ?? $this->ask('Email');

        // Получение пароля: из аргумента или скрытый ввод через консоль
        $password = $this->argument('password') ?? $this->secret('Пароль');

        // Получение имени: из опции --name или запрос с значением по умолчанию
        $name = $this->option('name') ?? $this->ask('Имя', 'Администратор');

        // Создание записи пользователя в базе данных
        $user = User::create([
            'full_name' => $name,                    // Полное имя администратора
            'email' => $email,                       // Уникальный email для входа
            'password' => Hash::make($password),     // Хеширование пароля алгоритмом bcrypt
            'role' => 'admin',                       // Назначение роли администратора
            'email_verified_at' => now(),            // Автоматическое подтверждение email
        ]);

        // Вывод сообщения об успешном создании администратора
        $this->info("Админ создан: $email");

        // Возврат кода успеха
        return 0;
    }
}