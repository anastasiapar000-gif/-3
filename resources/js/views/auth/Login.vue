<template>
  <!-- Корневой контейнер страницы входа: центрирование по вертикали и горизонтали, минимальная высота -->
  <div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
      
      <!-- Заголовок формы входа -->
      <h2 class="text-3xl font-bold text-center mb-8 uppercase tracking-widest">Вход</h2>
      
      <!-- Форма авторизации: отправка предотвращает перезагрузку страницы -->
      <form @submit.prevent="handleLogin" class="space-y-6">
        
        <!-- Поле ввода email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
          >
        </div>

        <!-- Поле ввода пароля -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Пароль</label>
          <input 
            v-model="form.password" 
            type="password" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
          >
        </div>

        <!-- Кнопка отправки формы: блокируется во время выполнения запроса -->
        <button 
          type="submit"
          :disabled="isLoading"
          class="w-full bg-red-600 text-white py-3 uppercase tracking-widest hover:bg-red-700 transition disabled:opacity-50"
        >
          {{ isLoading ? 'Вход...' : 'Войти' }}
        </button>
      </form>

      <!-- Ссылка на страницу регистрации для новых пользователей -->
      <p class="mt-6 text-center text-sm text-gray-600">
        Нет аккаунта? 
        <router-link to="/register" class="text-red-600 hover:underline">Зарегистрироваться</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивного хука Vue для создания переменных состояния
import { ref } from 'vue';

// Импорт хуков роутера для навигации и доступа к параметрам маршрута
import { useRouter, useRoute } from 'vue-router';

// Импорт хранилища аутентификации (Pinia) для работы с токеном и данными пользователя
// Убедитесь, что путь к файлу соответствует структуре вашего проекта
import { useAuthStore } from '../../stores/auth';

// Инициализация роутера и текущего маршрута
const router = useRouter();
const route = useRoute();

// Получение экземпляра хранилища аутентификации
const authStore = useAuthStore();

// === STATE: Реактивные переменные состояния компонента ===

// Объект формы: данные для авторизации (email и пароль)
const form = ref({
  email: '',
  password: ''
});

// Флаг выполнения асинхронного запроса: блокирует кнопку во время входа
const isLoading = ref(false);

// === МЕТОДЫ: Обработчики действий ===

// Обработчик отправки формы входа
const handleLogin = async () => {
  // Устанавливаем флаг загрузки для блокировки интерфейса
  isLoading.value = true;
  
  try {
    // Вызов метода login из хранилища аутентификации
    // Метод отправляет POST-запрос на сервер, получает токен и данные пользователя
    await authStore.login(form.value.email, form.value.password);
    
    // После успешного входа хранилище обновлено: проверяем роль пользователя через геттер isAdmin
    if (authStore.isAdmin) {
      // Администраторы перенаправляются в панель управления заказами
      router.push('/admin/orders');
    } else {
      // Обычные пользователи:
      // 1. Если был параметр ?redirect= в URL (попытка доступа к защищённой странице) — идём туда
      // 2. Иначе — перенаправляем в каталог товаров
      const redirectPath = route.query.redirect;
      router.push(redirectPath || '/catalog');
    }
    
  } catch (error) {
    // Логирование ошибки в консоль разработчика
    console.error('Ошибка входа:', error);
    
    // Формирование читаемого сообщения об ошибке для пользователя
    // Приоритет: сообщение от сервера → текст ошибки → общее сообщение
    alert('Ошибка входа: ' + (error.response?.data?.message || error.message || 'Неверные данные'));
    
  } finally {
    // Сброс флага загрузки независимо от результата запроса
    isLoading.value = false;
  }
};
</script>