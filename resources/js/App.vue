<template>
  <!-- Корневой контейнер приложения: минимальная высота на весь экран, градиентный фон, вертикальная флекс-раскладка -->
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-white text-gray-900 font-sans flex flex-col">
    
    <!-- === ШАПКА САЙТА (HEADER) === -->
    <!-- Фиксированная шапка: всегда видна при прокрутке, тёмная тема, тень, плавные переходы -->
    <header class="fixed top-0 w-full z-50 bg-black border-b border-gray-800 shadow-lg transition-all duration-300">
      <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">

        <!-- === ЛОГОТИП === -->
        <!-- Ссылка на главную страницу: при наведении меняется цвет текста и появляется подчёркивание -->
        <router-link to="/" class="group flex items-center gap-2">
          <div class="relative">
            <!-- Текст логотипа: кастомный шрифт, верхний регистр, увеличенный межбуквенный интервал -->
            <span 
              class="text-5xl font-bold tracking-[0.35em] text-white group-hover:text-red-600 transition-all duration-300"
              style="font-family: 'Stieglitz SP', serif; letter-spacing: 0.15em; text-transform: uppercase;"
            >
              ZIMA
            </span>
            <!-- Анимированная линия под логотипом: появляется при наведении -->
            <div class="absolute -bottom-1 left-0 w-0 h-0.5 bg-red-600 group-hover:w-full transition-all duration-500"></div>
          </div>
        </router-link>

        <!-- === НАВИГАЦИОННОЕ МЕНЮ (Десктоп) === -->
        <!-- Скрыто на мобильных устройствах, показывается на экранах от 768px (md) -->
        <nav class="hidden md:flex gap-10 items-center">
          
          <!-- Ссылка на каталог: анимация подчёркивания при наведении -->
          <router-link 
            to="/catalog" 
            class="group relative text-xs uppercase tracking-[0.2em] text-white hover:text-red-600 transition-all duration-300 py-2"
          >
            Каталог
            <!-- Анимированная линия-подчёркивание -->
            <span class="absolute bottom-0 left-0 w-0 h-px bg-red-600 group-hover:w-full transition-all duration-300"></span>
          </router-link>
          
          <!-- Ссылка на страницу "О нас" -->
          <router-link 
            to="/about" 
            class="group relative text-xs uppercase tracking-[0.2em] text-white hover:text-red-600 transition-all duration-300 py-2"
          >
            О нас
            <span class="absolute bottom-0 left-0 w-0 h-px bg-red-600 group-hover:w-full transition-all duration-300"></span>
          </router-link>

          <!-- Ссылка на страницу контактов -->
          <router-link 
            to="/contacts" 
            class="group relative text-xs uppercase tracking-[0.2em] text-white hover:text-red-600 transition-all duration-300 py-2"
          >
            Контакты
            <span class="absolute bottom-0 left-0 w-0 h-px bg-red-600 group-hover:w-full transition-all duration-300"></span>
          </router-link>
        </nav>

        <!-- === ПРАВАЯ ЧАСТЬ ШАПКИ: Авторизация и корзина === -->
        <div class="flex items-center gap-5">
          
          <!-- Компонент меню пользователя: отображает аватар, имя, ссылки на профиль и выход -->
          <UserMenu />
          
          <!-- Иконка корзины: ссылка на страницу корзины с бейджем количества товаров -->
          <router-link 
            to="/cart" 
            class="relative group p-2 rounded-full hover:bg-gray-900 transition-all duration-300"
          >
            <!-- Иконка корзины (SVG): меняет цвет и масштаб при наведении -->
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke-width="1.2" 
              stroke="currentColor" 
              class="w-6 h-6 text-white group-hover:text-red-600 transition-all duration-300 group-hover:scale-110"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" 
              />
            </svg>
            
            <!-- Бейдж с количеством товаров в корзине: показывается только если товаров > 0 -->
            <span 
              v-if="cartTotalItems > 0" 
              class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full shadow-lg animate-bounce"
            >
              {{ cartTotalItems }}
            </span>
          </router-link>
          
          <!-- Кнопка открытия мобильного меню: показывается только на мобильных устройствах -->
          <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white hover:text-red-600 transition-colors p-2">
            <!-- Иконка гамбургер/крестик: меняется в зависимости от состояния mobileMenuOpen -->
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke-width="1.5" 
              stroke="currentColor" 
              class="w-6 h-6"
            >
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                :d="mobileMenuOpen ? 'M6 18L18 6M6 6l12 12' : 'M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5'"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- === МОБИЛЬНОЕ МЕНЮ === -->
      <!-- Показывается только на мобильных устройствах при открытии -->
      <div v-if="mobileMenuOpen" class="md:hidden bg-black border-t border-gray-800 py-4 px-6 space-y-4">
        <!-- Ссылки меню: при клике закрывают мобильное меню -->
        <router-link 
          to="/catalog" 
          @click="mobileMenuOpen = false"
          class="block text-sm uppercase tracking-[0.2em] text-white hover:text-red-600 transition-colors py-2"
        >
          Каталог
        </router-link>
        <router-link 
          to="/about" 
          @click="mobileMenuOpen = false"
          class="block text-sm uppercase tracking-[0.2em] text-white hover:text-red-600 transition-colors py-2"
        >
          О нас
        </router-link>
        
        <!-- Ссылка на контакты (мобильная версия) -->
        <router-link 
          to="/contacts" 
          @click="mobileMenuOpen = false"
          class="block text-sm uppercase tracking-[0.2em] text-white hover:text-red-600 transition-colors py-2"
        >
          Контакты
        </router-link>
        
        <!-- Ссылка на корзину с указанием количества товаров -->
        <router-link 
          to="/cart" 
          @click="mobileMenuOpen = false"
          class="block text-sm uppercase tracking-[0.2em] text-white hover:text-red-600 transition-colors py-2"
        >
          Корзина {{ cartTotalItems > 0 ? `(${cartTotalItems})` : '' }}
        </router-link>
      </div>
    </header>

    <!-- === ОСНОВНОЙ КОНТЕНТ === -->
    <!-- Отступ сверху (pt-20) компенсирует высоту фиксированной шапки -->
    <main class="flex-grow pt-20">
      <!-- Динамический компонент: здесь рендерится текущий маршрут приложения -->
      <router-view />
    </main>

    <!-- === ПОДВАЛ САЙТА (FOOTER) === -->
    <!-- Тёмный фон, отступы, прижат к низу страницы благодаря flex-grow у main -->
    <footer class="bg-black text-white py-12 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Сетка футера: 1 колонка на мобильных, 4 на десктопе -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          
          <!-- Блок 1: Логотип и соцсети -->
<div class="md:col-span-1">
  <!-- Логотип в футере -->
  <span 
    class="text-5xl font-bold tracking-[0.35em] text-white group-hover:text-red-600 transition-all duration-300"
    style="font-family: 'Stieglitz SP', serif; letter-spacing: 0.15em; text-transform: uppercase;"
  >
    ZIMA
  </span>
  
  
  <div class="flex gap-4 mt-4">
    
 <!-- Иконка ВКонтакте - ИСПРАВЛЕННАЯ -->
<a 
  href="https://vk.com/zimajewelry" 
  target="_blank" 
  class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition text-white font-bold text-base"
  aria-label="VK"
>
  ВК
</a>
    <!-- Иконка Telegram -->
    <a 
      href="https://t.me/zimajewelrybrand" 
      target="_blank" 
      class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-gray-700 transition"
      aria-label="Telegram"
    >
      <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M9.78 18.65l.28-4.23 7.68-6.92c.34-.3-.07-.45-.52-.19L7.74 13.3 3.64 12c-.89-.25-.87-.76.19-1.18l15.92-6.13c.73-.33 1.43.18 1.16 1.32L18.18 20.2c-.2.9-.73 1.12-1.48.7l-4.1-3.02-1.98 1.9c-.22.22-.4.4-.82.4z"/>
      </svg>
    </a>
    
  </div>
</div>
          
          <!-- Блок 2: Ссылки на категории каталога -->
          <div>
            <h4 class="font-semibold mb-4">Каталог</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><router-link to="/catalog" class="hover:text-white transition">Кольца</router-link></li>
              <li><router-link to="/catalog" class="hover:text-white transition">Подвески</router-link></li>
              <li><router-link to="/catalog" class="hover:text-white transition">Броши</router-link></li>
              <li><router-link to="/catalog" class="hover:text-white transition">Серьги</router-link></li>
            </ul>
          </div>
          
          <!-- Блок 3: Ссылки на услуги с якорями -->
          <div>
            <h4 class="font-semibold mb-4">Услуги</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <!-- Ссылка с якорем #engraving для прокрутки к секции гравировки -->
              <li>
                <router-link to="/services#engraving" class="hover:text-white transition">
                  Индивидуальная гравировка
                </router-link>
              </li>
              <!-- Ссылка с якорем #coating для прокрутки к секции покрытий -->
              <li>
                <router-link to="/services#coating" class="hover:text-white transition">
                  Гальванические покрытия
                </router-link>
              </li>
              <!-- Ссылка с якорем #cleaning для прокрутки к секции чистки -->
              <li>
                <router-link to="/services#cleaning" class="hover:text-white transition">
                  Бесплатная чистка украшений
                </router-link>
              </li>
            </ul>
          </div>
          
          <!-- Блок 4: Полезные ссылки -->
          <div>
            <h4 class="font-semibold mb-4">Полезное</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li><router-link to="/about" class="hover:text-white transition">О нас</router-link></li>
              <li><router-link to="/delivery" class="hover:text-white transition">Доставка и оплата</router-link></li>
              <li><router-link to="/contacts" class="hover:text-white transition">Контакты</router-link></li>
            </ul>
          </div>
        </div>
        
        <!-- Нижняя граница футера: место для копирайта (сейчас пустое) -->
        <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
        </div>
      </div>
    </footer>

  </div>
</template>

<script setup>
// Импорт реактивного хука Vue для создания переменных состояния
import { ref } from 'vue';

// Импорт композиции useCart для получения данных о корзине покупок
import { useCart } from './composables/useCart';

// Импорт компонента меню пользователя (отображает аватар, имя, ссылки на профиль и выход)
import UserMenu from './Components/UserMenu.vue';

// === ИНИЦИАЛИЗАЦИЯ ===

// Получение вычисляемого свойства totalItems из композиции корзины
// Переименовываем в cartTotalItems для ясности использования в шапке
const { totalItems: cartTotalItems } = useCart();

// Состояние мобильного меню: управляет видимостью выпадающего меню на мобильных устройствах
const mobileMenuOpen = ref(false);
</script>