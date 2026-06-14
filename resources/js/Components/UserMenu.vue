<template>
  <!-- Корневой контейнер для авторизованного пользователя -->
  <div v-if="authStore.isAuthenticated" class="relative">
    
    <!-- Кнопка-триггер: открывает/закрывает меню, отображает аватар или инициал -->
    <button 
      @click="menuOpen = !menuOpen" 
      class="group flex items-center gap-2 focus:outline-none"
    >
      <div class="w-9 h-9 rounded-full bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center text-red-600 font-bold text-xs uppercase tracking-wider border-2 border-white shadow-md group-hover:shadow-lg group-hover:scale-105 transition-all duration-300">
        <!-- Отображаем первую букву имени, если нет аватара -->
        <span v-if="!avatarUrl">{{ userInitial }}</span>
        <!-- Отображаем аватар, если он есть -->
        <img v-else :src="avatarUrl" class="w-full h-full rounded-full object-cover" alt="Avatar">
      </div>
    </button>
    
    <!-- Выпадающее меню: показывается только при menuOpen = true -->
    <div 
      v-if="menuOpen" 
      class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 z-50 animate-fade-in"
    >
      <!-- Блок с информацией о пользователе -->
      <div class="px-4 py-2 border-b border-gray-100">
        <p class="text-xs text-gray-500 uppercase tracking-wider">Вы вошли как</p>
        <!-- Имя пользователя (обрезаем если длинное) -->
        <p class="text-sm font-semibold text-gray-900 truncate">{{ userName }}</p>
        
        <!-- Отображаем метку "Администратор" только для пользователей с ролью admin -->
        <p v-if="authStore.isAdmin" class="text-[10px] text-red-600 font-bold uppercase mt-0.5">
          Администратор
        </p>
      </div>
      
      <!-- Ссылка на дашборд: доступна только администраторам -->
      <router-link 
        v-if="authStore.isAdmin"
        to="/admin/dashboard" 
        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200 bg-red-50/30"
        @click="menuOpen = false"
      >
        <!-- Иконка дашборда (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Дашборд
      </router-link>

      <!-- Ссылка на профиль: доступна всем авторизованным пользователям -->
      <router-link 
        to="/profile" 
        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200"
        @click="menuOpen = false"
      >
        <!-- Иконка профиля (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        Профиль
      </router-link>
      
      <!-- Ссылка на заказы с параметром ?tab=orders для открытия нужной вкладки -->
      <router-link 
        to="/profile?tab=orders" 
        class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 transition-all duration-200"
        @click="menuOpen = false"
      >
        <!-- Иконка заказов (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
        </svg>
        Мои заказы
      </router-link>
      
      <!-- Визуальный разделитель -->
      <div class="border-t border-gray-100 my-1"></div>
      
      <!-- Кнопка выхода: вызывает метод logout() -->
      <button 
        @click="logout" 
        class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-all duration-200"
      >
        <!-- Иконка выхода (SVG) -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
        </svg>
        Выйти
      </button>
    </div>
  </div>
  
  <!-- Блок для неавторизованных пользователей: кнопки входа и регистрации -->
  <div v-else class="flex items-center gap-3">
    <router-link 
      to="/login" 
      class="text-xs uppercase tracking-[0.15em] text-gray-600 hover:text-red-600 transition-all duration-300 py-2 px-3"
    >
      Войти
    </router-link>
    <router-link 
      to="/register" 
      class="text-xs uppercase tracking-[0.15em] px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-500 text-white rounded-full hover:from-red-700 hover:to-red-600 transition-all duration-300 shadow-md hover:shadow-lg hover:scale-105 font-medium"
    >
      Регистрация
    </router-link>
  </div>
</template>

<script setup>
// Импорт хуков Vue для реактивности и жизненного цикла
import { ref, computed, onMounted, onUnmounted } from 'vue';
// Импорт роутера для программной навигации
import { useRouter } from 'vue-router';
// Импорт Pinia-хранилища для работы с аутентификацией
import { useAuthStore } from '../stores/auth';

// Инициализация хранилища и роутера
const authStore = useAuthStore();
const router = useRouter();
// Реактивная переменная для управления состоянием меню (открыто/закрыто)
const menuOpen = ref(false);

// Вычисляемое свойство: возвращает первую букву имени пользователя для аватара-заглушки
const userInitial = computed(() => {
  const name = authStore.user?.full_name || authStore.user?.name || 'U';
  return name.charAt(0).toUpperCase();
});

// Вычисляемое свойство: возвращает отображаемое имя пользователя
const userName = computed(() => {
  return authStore.user?.full_name || authStore.user?.name || 'Пользователь';
});

// Вычисляемое свойство: возвращает URL аватара или null
const avatarUrl = computed(() => authStore.user?.avatar || null);

// Метод выхода: закрывает меню, вызывает логаут из хранилища, перенаправляет на главную
const logout = async () => {
  menuOpen.value = false;
  await authStore.logout();
  router.push('/');
};

// Обработчик клика вне компонента: закрывает меню, если клик был не по его области
const closeMenu = (e) => {
  if (!e.target.closest('.relative')) {
    menuOpen.value = false;
  }
};

// Хук жизненного цикла: добавляем слушатель клика на документ при монтировании компонента
onMounted(() => {
  document.addEventListener('click', closeMenu);
});

// Хук жизненного цикла: удаляем слушатель при уничтожении компонента (предотвращаем утечки памяти)
onUnmounted(() => {
  document.removeEventListener('click', closeMenu);
});
</script>

<style scoped>
/* Класс для анимации появления меню */
.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}

/* Ключевые кадры анимации: плавное появление со сдвигом вверх */
@keyframes fadeIn {
  from { 
    opacity: 0; 
    transform: translateY(-8px) scale(0.95); 
  }
  to { 
    opacity: 1; 
    transform: translateY(0) scale(1); 
  }
}
</style>