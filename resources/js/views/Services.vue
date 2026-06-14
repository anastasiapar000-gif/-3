<template>
  <div class="min-h-screen bg-white">
    
    <!-- 🔧 ЗАГОЛОВОК В СТИЛЕ КАТАЛОГА -->
    <div class="relative border-b border-gray-800 overflow-hidden">
      <!-- Фоновое изображение -->
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://i.pinimg.com/736x/df/62/1e/df621eb33fbe80474164151ab0e02d79.jpg');"
      >
        <!-- Тёмный оверлей -->
        <div class="absolute inset-0 bg-black/50"></div>
      </div>

      <!-- Контент заголовка -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold text-red-600 mb-4 tracking-[0.2em]">
            УСЛУГИ
          </h1>
          <p class="text-lg text-gray-300 tracking-wide">
            Профессиональный уход и персонализация ваших украшений
          </p>
        </div>
      </div>
    </div>

    <!-- 🔧 СПИСОК УСЛУГ -->
    <section id="services" class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Карточка 1: Гравировка -->
        <div id="engraving" class="flex flex-col lg:flex-row gap-12 items-center mb-20 pb-20 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
          <div class="lg:w-1/2 order-2 lg:order-1">
            <span class="inline-block px-4 py-1 bg-red-100 text-red-700 text-xs font-bold uppercase tracking-[0.2em] rounded-full mb-4">
              Персонализация
            </span>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Индивидуальная гравировка</h2>
            <p class="text-gray-600 mb-6 leading-relaxed">
              Нанесём на ваше украшение памятную дату, имя, координаты важного места или короткий текст. 
              Используем лазерную гравировку для максимальной точности и долговечности.
            </p>
            <ul class="space-y-3 text-gray-600 mb-8">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Лазерная гравировка на металле, камнях и эмали</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Любые шрифты и символы, включая кириллицу</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Срок выполнения: 1–3 рабочих дня</span>
              </li>
            </ul>
            <div class="flex items-center gap-4">
              <span class="text-2xl font-bold text-gray-900">от 500 ₽</span>
              <button @click="openOrderModal('Гравировка')" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                Заказать
              </button>
            </div>
          </div>
          <div class="lg:w-1/2 order-1 lg:order-2">
            <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
              <img 
                src=/public/images/gravirovka.jpg
                alt="Лазерная гравировка на кольце"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <!-- Карточка 2: Гальваника -->
        <div id="coating" class="flex flex-col lg:flex-row gap-12 items-center mb-20 pb-20 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
          <div class="lg:w-1/2">
            <span class="inline-block px-4 py-1 bg-red-100 text-red-700 text-xs font-bold uppercase tracking-[0.2em] rounded-full mb-4">
              Восстановление
            </span>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Гальванические покрытия</h2>
            <p class="text-gray-600 mb-6 leading-relaxed">
              Вернём украшению первоначальный блеск или изменим его цвет. 
              Наносим покрытия из родия, золота, серебра и других благородных металлов.
            </p>
            <ul class="space-y-3 text-gray-600 mb-8">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Родирование — защита от потемнения и царапин</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Позолота и посеребрение для изменения цвета</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Срок выполнения: 3–7 рабочих дней</span>
              </li>
            </ul>
            <div class="flex items-center gap-4">
              <span class="text-2xl font-bold text-gray-900">от 1 200 ₽</span>
              <button @click="openOrderModal('Гальваника')" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                Заказать
              </button>
            </div>
          </div>
          <div class="lg:w-1/2">
            <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
              <img 
                src=/public/images/galvanika.jpg
                alt="Процесс гальванического покрытия"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <!-- Карточка 3: Чистка -->
        <div id="cleaning" class="flex flex-col lg:flex-row gap-12 items-center">
          <div class="lg:w-1/2 order-2 lg:order-1">
            <span class="inline-block px-4 py-1 bg-red-100 text-red-700 text-xs font-bold uppercase tracking-[0.2em] rounded-full mb-4">
              Уход
            </span>
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Бесплатная чистка украшений</h2>
            <p class="text-gray-600 mb-6 leading-relaxed">
              При покупке любого украшения в нашем магазине — профессиональная чистка в подарок. 
              Также принимаем изделия на чистку отдельно.
            </p>
            <ul class="space-y-3 text-gray-600 mb-8">
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Ультразвуковая и паровая чистка</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Безопасно для всех видов камней и металлов</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <span>Срок выполнения: 30 минут при вас</span>
              </li>
            </ul>
            <div class="flex items-center gap-4">
              <span class="text-2xl font-bold text-green-600">Бесплатно*</span>
              <button @click="openOrderModal('Чистка')" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                Записаться
              </button>
            </div>
            <p class="mt-3 text-xs text-gray-400">*Бесплатно при покупке от 3 000 ₽. Отдельно — 300 ₽.</p>
          </div>
          <div class="lg:w-1/2 order-1 lg:order-2">
            <div class="relative aspect-square bg-gray-100 rounded-2xl overflow-hidden shadow-lg">
              <img 
                src=/public/images/chistka.jpg
                alt="Профессиональная чистка украшений"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- 🔧 КАК МЫ РАБОТАЕМ -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12 tracking-[0.15em]">
          КАК МЫ РАБОТАЕМ
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold">1</div>
            <h3 class="font-semibold text-gray-900 mb-2">Заявка</h3>
            <p class="text-sm text-gray-600">Оставьте заявку на сайте или позвоните нам</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold">2</div>
            <h3 class="font-semibold text-gray-900 mb-2">Доставка</h3>
            <p class="text-sm text-gray-600">Привезите украшение в мастерскую или отправьте курьером</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold">3</div>
            <h3 class="font-semibold text-gray-900 mb-2">Выполнение</h3>
            <p class="text-sm text-gray-600">Мастер выполнит работу в оговоренные сроки</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-red-600 text-white rounded-full flex items-center justify-center text-xl font-bold">4</div>
            <h3 class="font-semibold text-gray-900 mb-2">Получение</h3>
            <p class="text-sm text-gray-600">Заберите готовое изделие или получите доставкой</p>
          </div>
        </div>
      </div>
    </section>

    <!-- 🔧 FAQ -->
    <section class="py-20 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center text-gray-900 mb-12 tracking-[0.15em]">
          ЧАСТЫЕ ВОПРОСЫ
        </h2>
        
        <div class="space-y-4">
          <details class="group bg-gray-50 rounded-lg p-4 cursor-pointer" v-for="(faq, index) in faqs" :key="index">
            <summary class="flex justify-between items-center font-semibold text-gray-900 list-none">
              {{ faq.question }}
              <span class="transition group-open:rotate-180">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </span>
            </summary>
            <p class="mt-3 text-gray-600 text-sm leading-relaxed">
              {{ faq.answer }}
            </p>
          </details>
        </div>
      </div>
    </section>


    <!-- 🔧 МОДАЛЬНОЕ ОКНО ЗАКАЗА -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl" @click.stop>
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold text-gray-900">Заказ услуги</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>
        
        <form @submit.prevent="submitOrder" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Услуга</label>
            <select v-model="orderForm.service" class="w-full px-4 py-2 border rounded-lg" required>
              <option value="">Выберите услугу</option>
              <option value="Гравировка">Индивидуальная гравировка</option>
              <option value="Гальваника">Гальванические покрытия</option>
              <option value="Чистка">Бесплатная чистка украшений</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ваше имя *</label>
            <input v-model="orderForm.name" type="text" required class="w-full px-4 py-2 border rounded-lg">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
            <input v-model="orderForm.phone" type="tel" required class="w-full px-4 py-2 border rounded-lg" placeholder="+7 (___) ___-__-__">
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Комментарий</label>
            <textarea v-model="orderForm.comment" rows="3" class="w-full px-4 py-2 border rounded-lg" placeholder="Опишите, что нужно сделать..."></textarea>
          </div>
          
          <button 
            type="submit" 
            :disabled="submitting"
            class="w-full py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50"
          >
            {{ submitting ? 'Отправка...' : 'Отправить заявку' }}
          </button>
        </form>
        
        <p class="mt-4 text-xs text-gray-400 text-center">
          Нажимая кнопку, вы соглашаетесь с обработкой персональных данных
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue';

const showModal = ref(false);
const submitting = ref(false);

const orderForm = ref({
  service: '',
  name: '',
  phone: '',
  comment: ''
});

const faqs = [
  {
    question: 'Сколько времени занимает гравировка?',
    answer: 'Обычно 1–3 рабочих дня. Срочная гравировка (в день обращения) возможна за дополнительную плату.'
  },
  {
    question: 'Можно ли сделать гравировку на камне?',
    answer: 'Да, мы делаем лазерную гравировку на твёрдых камнях (фианит, топаз, агат). На мягких камнях и жемчуге гравировка не рекомендуется.'
  },
  {
    question: 'Как долго держится родиевое покрытие?',
    answer: 'При бережном ношении — от 6 месяцев до 2 лет. Срок зависит от интенсивности использования и ухода за украшением.'
  },
  {
    question: 'Можно ли заказать услугу без покупки украшения?',
    answer: 'Да, мы принимаем на обслуживание любые украшения, независимо от места покупки. Бесплатная чистка доступна только при покупке от 3 000 ₽.'
  }
];

// Открытие модального окна с предзаполнением услуги
const openOrderModal = (service = '') => {
  orderForm.value.service = service;
  showModal.value = true;
};

// Закрытие модального окна
const closeModal = () => {
  showModal.value = false;
  orderForm.value = { service: '', name: '', phone: '', comment: '' };
};

// Отправка заявки
const submitOrder = async () => {
  submitting.value = true;
  
  try {
    // Имитация отправки (замените на реальный API-запрос)
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    alert('Заявка отправлена! Мы свяжемся с вами в ближайшее время.');
    closeModal();
    
  } catch (error) {
    console.error('Ошибка отправки:', error);
    alert('Не удалось отправить заявку. Попробуйте позже или позвоните нам.');
  } finally {
    submitting.value = false;
  }
};
</script>

<style scoped>
/* Плавная прокрутка для якорных ссылок */
html {
  scroll-behavior: smooth;
}

/* Стилизация details/summary */
details summary::-webkit-details-marker {
  display: none;
}

/* Анимация стрелки в FAQ */
details summary span {
  transition: transform 0.2s ease;
}
</style>