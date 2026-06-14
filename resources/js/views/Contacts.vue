<template>
  <div class="min-h-screen bg-gray-50">
    
    <!-- Заголовок с фоновым изображением -->
    <div class="relative border-b border-gray-800 overflow-hidden">
      <!-- Фоновое изображение -->
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://i.pinimg.com/736x/df/62/1e/df621eb33fbe80474164151ab0e02d79.jpg');"
      >
        <!-- Тёмный оверлей для читаемости текста -->
        <div class="absolute inset-0 bg-black/60"></div>
      </div>

      <!-- Контент заголовка (обязательно внутри relative) -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold text-red-600 mb-3 tracking-[0.3em] uppercase">
            Контакты
          </h1>
          <div class="w-20 h-0.5 bg--600 mx-auto mb-4"></div>
          <p class="text-gray-300 text-sm tracking-[0.2em] uppercase">
            Свяжитесь с нами любым удобным способом
          </p>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        
        <!-- Левая колонка: Информация -->
        <div class="space-y-8">
          <div>
            <h2 class="text-2xl font-bold tracking-[0.2em] text-gray-900 mb-6 uppercase">
              Шоурум ZIMA
            </h2>
            <p class="text-gray-600 leading-relaxed mb-6">
              Приходите посмотреть украшения вживую. Мы подберем идеальный подарок или украшение специально для вас.
            </p>
          </div>

          <div class="space-y-4">
            <!-- Адрес -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Адрес</p>
                <p class="text-gray-900 font-medium">г. Иркутск, ул. Ленина, 5А</p>
                <p class="text-sm text-gray-500 mt-1">Индекс: 664025</p>
              </div>
            </div>

            <!-- Телефон -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Телефон</p>
                <a href="tel:+79242703153" class="text-gray-900 font-medium hover:text-red-600 transition">
                  +7 (924) 270-31-53
                </a>
              </div>
            </div>

            <!-- Email -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Email</p>
                <a href="mailto:info@zima-shop.ru" class="text-gray-900 font-medium hover:text-red-600 transition">
                  info@zimajewelry.ru
                </a>
              </div>
            </div>

            <!-- Время работы -->
            <div class="flex items-start gap-4">
              <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Режим работы</p>
                <p class="text-gray-900 font-medium">Пн-Пт: 10:00 — 20:00</p>
                <p class="text-gray-900 font-medium">Сб-Вс: 11:00 — 18:00</p>
              </div>
            </div>
          </div>

          <!-- Соцсети -->
          <div class="pt-8 border-t border-gray-200">
            <p class="text-xs text-gray-500 uppercase tracking-wider mb-4">Мы в соцсетях</p>
            <div class="flex gap-4">
              <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors group">
                <svg class="w-5 h-5 text-gray-600 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.16.16-.295.295-.605.295l.213-3.054 5.56-5.022c.242-.213-.054-.334-.373-.121L8.32 13.617l-2.96-.924c-.64-.203-.658-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.94z"/></svg>
              </a>
              <a href="#" class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-red-600 hover:text-white transition-colors group">
                <svg class="w-5 h-5 text-gray-600 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
              </a>
            </div>
          </div>
        </div>

        <!-- Правая колонка: Форма -->
        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
          <h3 class="text-xl font-bold tracking-[0.15em] text-gray-900 mb-6 uppercase text-center">
            Напишите нам
          </h3>
          
          <form @submit.prevent="sendMessage" class="space-y-5">
            
            <!-- Сообщение об успехе -->
            <div v-if="success" class="p-4 bg-green-50 border border-green-200 rounded-lg text-center">
              <p class="text-green-800 text-sm font-medium uppercase tracking-wide">Сообщение отправлено</p>
            </div>

            <!-- Сообщение об ошибке -->
            <div v-if="errorMessage" class="p-4 bg-red-50 border border-red-200 rounded-lg text-center">
              <p class="text-red-800 text-sm font-medium uppercase tracking-wide">{{ errorMessage }}</p>
            </div>

            <div>
              <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Ваше имя *</label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                :disabled="isLoading"
                class="w-full px-4 py-3 border border-gray-300 rounded focus:ring-1 focus:ring-red-600 focus:border-red-600 transition disabled:opacity-50 text-sm tracking-wide"
                placeholder="Введите имя"
              >
            </div>

            <div>
              <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Телефон или Email *</label>
              <input 
                v-model="form.contact" 
                type="text" 
                required
                :disabled="isLoading"
                class="w-full px-4 py-3 border border-gray-300 rounded focus:ring-1 focus:ring-red-600 focus:border-red-600 transition disabled:opacity-50 text-sm tracking-wide"
                placeholder="Для связи"
              >
            </div>

            <div>
              <label class="block text-xs uppercase tracking-widest text-gray-500 mb-2">Сообщение *</label>
              <textarea 
                v-model="form.message" 
                rows="4" 
                required
                :disabled="isLoading"
                class="w-full px-4 py-3 border border-gray-300 rounded focus:ring-1 focus:ring-red-600 focus:border-red-600 transition resize-none disabled:opacity-50 text-sm tracking-wide"
                placeholder="Ваш вопрос..."
              ></textarea>
            </div>

            <button 
              type="submit" 
              :disabled="isLoading"
              class="w-full bg-red-600 text-white py-4 uppercase tracking-[0.25em] hover:bg-red-700 transition disabled:opacity-50 font-medium text-sm rounded"
            >
              {{ isLoading ? 'Отправка...' : 'Отправить' }}
            </button>
          </form>
        </div>

      </div>

      <!-- Карта в темной рамке -->
      <div class="mt-20">
        <div class="bg- p-1 rounded-xl">
          <div class="w-full rounded-lg overflow-hidden" style="height: 450px;">
            <iframe 
              src="https://yandex.ru/map-widget/v1/?ll=104.282361%2C52.282852&mode=whatshere&whatshere%5Bpoint%5D=104.281287%2C52.283561&whatshere%5Bzoom%5D=17&z=17.8" 
              width="100%" 
              height="100%" 
              frameborder="0" 
              allowfullscreen="true"
              style="display: block; border: none;"
              title="Карта проезда"
            ></iframe>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
// ============================================================================
// ИМПОРТЫ ЗАВИСИМОСТЕЙ
// ============================================================================
import { ref } from 'vue';
import axios from 'axios';

// ============================================================================
// СОСТОЯНИЕ ФОРМЫ (РЕАКТИВНЫЕ ДАННЫЕ)
// ============================================================================
// Данные формы обратной связи
const form = ref({
  name: '',      // Имя отправителя
  contact: '',   // Контактные данные (электронная почта или телефон)
  message: ''    // Текст сообщения
});

// Флаги состояния процесса отправки
const isLoading = ref(false);    // Индикатор загрузки во время отправки запроса
const success = ref(false);      // Флаг успешной отправки сообщения
const errorMessage = ref('');    // Текст ошибки для отображения пользователю

// ============================================================================
// ОБРАБОТКА ОТПРАВКИ СООБЩЕНИЯ
// ============================================================================
// Основная функция отправки данных формы на сервер
const sendMessage = async () => {
  // Сброс предыдущих состояний перед новой попыткой отправки
  isLoading.value = true;
  success.value = false;
  errorMessage.value = '';

  try {
    // Отправка POST-запроса к эндпоинту /api/contact с данными формы
    const response = await axios.post('http://127.0.0.1:8000/api/contact', {
      name: form.value.name,
      contact: form.value.contact,
      message: form.value.message,
    });

    // Логирование успешного ответа сервера для отладки
    console.log('Ответ сервера:', response.data);
    
    // Установка флага успеха и очистка полей формы
    success.value = true;
    form.value = { name: '', contact: '', message: '' };
    
    // Автоматическое скрытие сообщения об успехе через 5 секунд
    setTimeout(() => { success.value = false; }, 5000);
    
  } catch (err) {
    // Логирование ошибки для диагностики проблем
    console.error('Ошибка отправки:', err);
    
    // Обработка ошибок валидации (статус 422 - Unprocessable Entity)
    if (err.response?.status === 422) {
      const errors = err.response.data.errors;
      // Извлечение первого сообщения об ошибке из объекта валидации Laravel
      errorMessage.value = Object.values(errors).flat()[0];
    } else {
      // Обработка сетевых ошибок и ошибок сервера (500, 401, 403 и др.)
      errorMessage.value = err.response?.data?.message || 'Ошибка. Попробуйте позже.';
    }
    
    // Автоматическое скрытие сообщения об ошибке через 5 секунд
    setTimeout(() => { errorMessage.value = ''; }, 5000);
    
  } finally {
    // Завершение процесса отправки независимо от результата запроса
    isLoading.value = false;
  }
};
</script>