<template>
  <!-- Корневой контейнер страницы регистрации: центрирование, минимальная высота, отступы -->
  <div class="min-h-[80vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
      
      <!-- Заголовок формы регистрации -->
      <h2 class="text-3xl font-bold text-center mb-8 uppercase tracking-widest">Регистрация</h2>
      
      <!-- Форма регистрации: отправка предотвращает перезагрузку страницы -->
      <form @submit.prevent="handleRegister" class="space-y-6">
        
        <!-- Поле ввода имени пользователя -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Имя</label>
          <input 
            v-model="form.name" 
            type="text" 
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
            placeholder="Ваше имя"
          >
        </div>

        <!-- Поле ввода email (обязательное) -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
          <input 
            v-model="form.email" 
            type="email" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
            placeholder="your@email.com"
          >
        </div>

        <!-- Поле ввода пароля с индикатором сложности -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Пароль</label>
          <input 
            v-model="form.password" 
            @input="checkPasswordStrength"
            type="password" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
            placeholder="••••••••"
          >
          
          <!-- Индикатор силы пароля: показывается только при вводе пароля -->
          <div v-if="form.password" class="mt-2">
            <!-- Прогресс-бар силы пароля -->
            <div class="flex items-center gap-2 mb-1">
              <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div 
                  class="h-full transition-all duration-300 rounded-full"
                  :class="passwordStrengthClass"
                  :style="{ width: passwordStrength + '%' }"
                ></div>
              </div>
              <!-- Текстовая метка силы пароля -->
              <span class="text-xs font-medium" :class="passwordStrengthTextColor">
                {{ passwordStrengthText }}
              </span>
            </div>
            
            <!-- Список требований к паролю с визуальной индикацией выполнения -->
            <div class="grid grid-cols-2 gap-1 text-xs">
              <span :class="passwordRules.length ? 'text-green-600' : 'text-gray-400'">
                {{ passwordRules.length ? '✓' : '○' }} Мин. 8 символов
              </span>
              <span :class="passwordRules.uppercase ? 'text-green-600' : 'text-gray-400'">
                {{ passwordRules.uppercase ? '✓' : '○' }} Заглавная буква
              </span>
              <span :class="passwordRules.lowercase ? 'text-green-600' : 'text-gray-400'">
                {{ passwordRules.lowercase ? '✓' : '○' }} Строчная буква
              </span>
              <span :class="passwordRules.number ? 'text-green-600' : 'text-gray-400'">
                {{ passwordRules.number ? '✓' : '○' }} Цифра
              </span>
              <span :class="passwordRules.special ? 'text-green-600' : 'text-gray-400'">
                {{ passwordRules.special ? '✓' : '○' }} Спецсимвол
              </span>
            </div>
          </div>
        </div>

        <!-- Поле подтверждения пароля с проверкой совпадения -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Подтвердите пароль</label>
          <input 
            v-model="form.password_confirmation" 
            type="password" 
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-600 focus:border-transparent"
            placeholder="••••••••"
          >
          <!-- Сообщение об ошибке: показывается при несовпадении паролей -->
          <p v-if="form.password_confirmation && form.password !== form.password_confirmation" 
             class="mt-1 text-xs text-red-600">
            Пароли не совпадают
          </p>
        </div>

        <!-- Кнопка регистрации: блокируется при загрузке или недостаточной сложности пароля -->
        <button 
          type="submit"
          :disabled="isLoading || !isPasswordStrongEnough"
          class="w-full bg-red-600 text-white py-3 uppercase tracking-widest hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {{ isLoading ? 'Регистрация...' : 'Зарегистрироваться' }}
        </button>
      </form>

      <!-- Ссылка на страницу входа для существующих пользователей -->
      <p class="mt-6 text-center text-sm text-gray-600">
        Уже есть аккаунт? 
        <router-link to="/login" class="text-red-600 hover:underline">Войти</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивных хуков Vue для создания переменных и вычисляемых свойств
import { ref, computed } from 'vue';

// Импорт хука роутера для программной навигации после успешной регистрации
import { useRouter } from 'vue-router';

// Импорт хранилища аутентификации (Pinia) для вызова метода регистрации
// Убедитесь, что путь к файлу соответствует структуре вашего проекта
import { useAuthStore } from '../../stores/auth'; 

// Инициализация роутера и хранилища аутентификации
const router = useRouter();
const authStore = useAuthStore();

// === STATE: Реактивные переменные состояния компонента ===

// Объект формы: данные для регистрации нового пользователя
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Флаг выполнения асинхронного запроса: блокирует кнопку во время регистрации
const isLoading = ref(false);

// === STATE: Переменные для проверки сложности пароля ===

// Процент выполнения требований к паролю (0-100)
const passwordStrength = ref(0);

// Объект правил пароля: каждое свойство отражает выполнение конкретного требования
const passwordRules = ref({
  length: false,      // Минимум 8 символов
  uppercase: false,   // Наличие заглавной буквы
  lowercase: false,   // Наличие строчной буквы
  number: false,      // Наличие цифры
  special: false      // Наличие специального символа
});

// === МЕТОДЫ: Проверка сложности пароля ===

// Проверка пароля на соответствие требованиям: вызывается при каждом вводе символа
const checkPasswordStrength = () => {
  const pwd = form.value.password;
  
  // Обновление статуса каждого правила на основе регулярных выражений
  passwordRules.value = {
    length: pwd.length >= 8,
    uppercase: /[A-Z]/.test(pwd),
    lowercase: /[a-z]/.test(pwd),
    number: /[0-9]/.test(pwd),
    special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pwd)
  };
  
  // Подсчёт количества выполненных правил
  const fulfilled = Object.values(passwordRules.value).filter(Boolean).length;
  
  // Расчёт процента силы пароля: (выполнено правил / всего правил) * 100
  passwordStrength.value = Math.round((fulfilled / 5) * 100);
};

// === COMPUTED: Вычисляемые свойства для индикатора пароля ===

// Класс цвета прогресс-бара в зависимости от силы пароля
const passwordStrengthClass = computed(() => {
  if (passwordStrength.value <= 40) return 'bg-red-500';
  if (passwordStrength.value <= 70) return 'bg-yellow-500';
  return 'bg-green-500';
});

// Класс цвета текста индикатора в зависимости от силы пароля
const passwordStrengthTextColor = computed(() => {
  if (passwordStrength.value <= 40) return 'text-red-600';
  if (passwordStrength.value <= 70) return 'text-yellow-600';
  return 'text-green-600';
});

// Текстовая метка силы пароля для отображения пользователю
const passwordStrengthText = computed(() => {
  if (!form.value.password) return '';
  if (passwordStrength.value <= 40) return 'Слабый';
  if (passwordStrength.value <= 70) return 'Средний';
  return 'Надёжный';
});

// Проверка: можно ли отправить форму (пароль достаточно надёжный + все поля заполнены)
const isPasswordStrongEnough = computed(() => {
  return passwordStrength.value >= 60 && // Минимум 3 из 5 правил выполнено
         form.value.password === form.value.password_confirmation && // Пароли совпадают
         form.value.name && // Имя заполнено
         form.value.email; // Email заполнен
});

// === МЕТОДЫ: Обработчики действий ===

// Функция перевода ошибок с английского на русский
const translateError = (message) => {
  const translations = {
    'The email has already been taken.': 'Этот email уже зарегистрирован. Пожалуйста, войдите или используйте другой email.',
    'The password must be at least 8 characters.': 'Пароль должен содержать минимум 8 символов.',
    'The password confirmation does not match.': 'Подтверждение пароля не совпадает.',
    'The name field is required.': 'Поле имени обязательно для заполнения.',
    'The email field is required.': 'Поле email обязательно для заполнения.',
    'The password field is required.': 'Поле пароля обязательно для заполнения.',
  };
  
  return translations[message] || message;
};

// Обработчик отправки формы регистрации
const handleRegister = async () => {
  // Дополнительная проверка перед отправкой: блокируем слабые пароли
  if (!isPasswordStrongEnough.value) {
    alert('Пароль недостаточно надёжный. Пожалуйста, усложните его.');
    return;
  }
  
  // Устанавливаем флаг загрузки для блокировки интерфейса
  isLoading.value = true;
  
  try {
    // Вызов метода register из хранилища аутентификации
    // Метод отправляет данные на сервер и при успехе сохраняет токен и пользователя
    await authStore.register(
      form.value.name,
      form.value.email,
      form.value.password,
      form.value.password_confirmation
    );
    
    // После успешной регистрации перенаправляем на главную страницу
    router.push('/');
    
  } catch (error) {
    // Логирование ошибки в консоль разработчика
    console.error('Ошибка регистрации:', error);
    
    // Формирование читаемого сообщения об ошибке для пользователя
    let message = 'Ошибка регистрации';
    
    // Приоритет сообщений: ошибки валидации по полям → общее сообщение от сервера → текст по умолчанию
    if (error.response?.data?.errors) {
      // Преобразуем объект ошибок валидации в строку и переводим на русский
      const errors = Object.values(error.response.data.errors).flat();
      const translatedErrors = errors.map(err => translateError(err));
      message = translatedErrors.join(', ');
    } else if (error.response?.data?.message) {
      // Переводим общее сообщение об ошибке
      message = translateError(error.response.data.message);
    }
    
    // Показ уведомления пользователю
    alert(message);
    
  } finally {
    // Сброс флага загрузки независимо от результата запроса
    isLoading.value = false;
  }
};
</script>