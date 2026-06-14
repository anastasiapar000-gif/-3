<template>
  <!-- Корневой контейнер админ-панели: фиксированная высота, фон, флекс-раскладка -->
  <div class="min-h-screen bg-gray-50 flex">
    
    <!-- Боковое меню (сайдбар): фиксированная ширина, тёмная тема, прокрутка, высокий z-index -->
    <aside class="w-64 bg-black text-white fixed h-full overflow-y-auto z-50">
      <div class="p-6">
        <!-- Заголовок админ-панели -->
        <h2 class="text-xl font-bold mb-6 tracking-wider">ZIMA ADMIN</h2>
        
        <!-- Навигационное меню: список ссылок с иконками -->
        <nav class="space-y-2">
          
          <!-- Ссылка на дашборд: активный стиль при точном совпадении пути -->
          <router-link 
            to="/admin/dashboard" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path === '/admin/dashboard' || $route.path === '/admin/dashboard/' ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка дашборда (SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <!-- Текст ссылки -->
            <span class="text-sm uppercase tracking-wider">Дашборд</span>
          </router-link>

          <!-- Ссылка на товары: активный стиль при начале пути с /admin/products -->
          <router-link 
            to="/admin/products" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path.startsWith('/admin/products') ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка товаров (SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-sm uppercase tracking-wider">Товары</span>
          </router-link>

          <!-- Ссылка на заказы: активный стиль при начале пути с /admin/orders -->
          <router-link 
            to="/admin/orders" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path.startsWith('/admin/orders') ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка заказов (SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <span class="text-sm uppercase tracking-wider">Заказы</span>
          </router-link>

          <!-- Ссылка на категории: активный стиль при начале пути с /admin/categories -->
          <router-link 
            to="/admin/categories" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path.startsWith('/admin/categories') ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка категорий (сетка, SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span class="text-sm uppercase tracking-wider">Категории</span>
          </router-link>

          <!-- Ссылка на пользователей: активный стиль при начале пути с /admin/users -->
          <router-link 
            to="/admin/users" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path.startsWith('/admin/users') ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка пользователей (люди, SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span class="text-sm uppercase tracking-wider">Пользователи</span>
          </router-link>

          <!-- Ссылка на сообщения: активный стиль при начале пути с /admin/messages -->
          <router-link 
            to="/admin/messages" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors"
            :class="$route.path.startsWith('/admin/messages') ? 'bg-red-700 text-white' : 'text-gray-400 hover:bg-gray-900 hover:text-white'"
          >
            <!-- Иконка сообщений (пузырь, SVG) -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="text-sm uppercase tracking-wider">Сообщения</span>
          </router-link>
        </nav>
      </div>

      <!-- Кнопка выхода: зафиксирована внизу сайдбара -->
      <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-gray-800">
        <button 
          @click="handleLogout"
          class="flex items-center gap-3 text-gray-400 hover:text-red-600 transition-colors w-full px-4 py-3"
        >
          <!-- Иконка выхода (стрелка, SVG) -->
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span class="text-sm uppercase tracking-wider">Выйти</span>
        </button>
      </div>
    </aside>

    <!-- Основной контент: отступ слева под ширину сайдбара -->
    <main class="flex-1 ml-64">
      
      <!-- Область контента страницы: максимальная ширина, отступы -->
      <div class="max-w-7xl mx-auto px-8 py-8">
        <!-- Динамический компонент: здесь рендерится текущий маршрут админ-панели -->
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
// Импорт роутера для программной навигации
import { useRouter } from 'vue-router';

// Инициализация роутера
const router = useRouter();

// Обработчик выхода из системы:
// 1. Удаляет токен авторизации из localStorage
// 2. Удаляет данные пользователя из localStorage
// 3. Перенаправляет на страницу входа в админ-панель
const handleLogout = () => {
  localStorage.removeItem('api_token');
  localStorage.removeItem('user');
  router.push('/admin/login');
};
</script>