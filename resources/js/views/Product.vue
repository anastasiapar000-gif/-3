<template>
  <!-- Корневой контейнер страницы товара: минимальная высота на весь экран, белый фон -->
  <div class="min-h-screen bg-white">
    
    <!-- Индикатор загрузки: показывается во время получения данных товара с сервера -->
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
    </div>

    <!-- Основной контент карточки товара: показывается после успешной загрузки данных -->
    <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
        
        <!-- ЛЕВАЯ КОЛОНКА: Галерея изображений товара -->
        <div class="space-y-4">
          
          <!-- Главное изображение товара: большое превью с эффектом увеличения при наведении -->
          <div class="relative aspect-square bg-gray-50 overflow-hidden rounded-lg border border-gray-200 group">
            <img
              v-if="mainImage"
              :src="mainImage"
              :alt="product?.name"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <!-- Заглушка: показывается если изображения отсутствуют или не загрузились -->
            <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
              <!-- Иконка заглушки (отсутствующее изображение, SVG) -->
              <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" 
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <!-- Индикатор фонового обновления данных: показывается во время авто-рефреша -->
            <div v-if="isRefreshing" class="absolute top-2 right-2 bg-white/90 px-2 py-1 rounded text-xs text-gray-600">
              Обновление...
            </div>
          </div>

          <!-- Сетка миниатюр: показывается только если у товара больше одного изображения -->
          <div v-if="allImages.length > 1" class="grid grid-cols-4 gap-3">
            <div 
              v-for="(image, index) in allImages" 
              :key="index"
              @click="activeImageIndex = index"
              class="aspect-square bg-gray-50 rounded-lg overflow-hidden border-2 cursor-pointer transition-all hover:shadow-md"
              :class="activeImageIndex === index 
                ? 'border-red-600 ring-2 ring-red-100' 
                : 'border-gray-200 hover:border-gray-400'"
            >
              <img 
                :src="image" 
                :alt="`${product?.name} - фото ${index + 1}`"
                class="w-full h-full object-cover"
              >
            </div>
          </div>
        </div>

        <!-- ПРАВАЯ КОЛОНКА: Информация о товаре и элементы управления -->
        <div class="flex flex-col">
          
          <!-- Заголовок товара и кнопка ручного обновления -->
          <div class="flex items-start justify-between">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight mb-4">
              {{ product.name }}
            </h1>
            <!-- Кнопка ручного обновления данных о наличии -->
            <button 
              @click="refreshProduct" 
              :disabled="isRefreshing"
              class="ml-4 p-2 text-gray-400 hover:text-red-600 transition disabled:opacity-50"
              title="Обновить наличие"
            >
              <!-- Иконка обновления с анимацией вращения во время загрузки -->
              <svg class="w-5 h-5" :class="{'animate-spin': isRefreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>

          <!-- Блок цены и статуса наличия -->
          <div class="flex items-baseline gap-4 mb-6">
            <!-- Цена товара с форматированием -->
            <span class="text-4xl font-bold text-red-600">{{ formatPrice(product.price) }}</span>
            
            <!-- Статус наличия: зелёный если есть, серый если нет -->
            <span v-if="totalStock > 0" class="text-sm text-green-600 font-medium uppercase tracking-wider">
              В наличии: {{ totalStock }} шт.
            </span>
            <span v-else class="text-sm text-gray-400 font-medium uppercase tracking-wider">
              Нет в наличии
            </span>
          </div>

          <!-- Описание товара: текстовый блок с базовой типографикой -->
          <div class="prose prose-sm text-gray-600 mb-8 leading-relaxed">
            <p>{{ product.description || 'Описание отсутствует' }}</p>
          </div>

          <!-- === ЛОГИКА ОТОБРАЖЕНИЯ РАЗМЕРА ИЛИ НАЛИЧИЯ === -->
          
          <!-- ВАРИАНТ А: Отображение для товаров с размерами (кольца) -->
          <div v-if="hasRealSizes" class="mb-8 border-t border-gray-100 pt-6">
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm font-bold text-gray-900 uppercase tracking-wide">Выберите размер *</span>
              <!-- Ссылка на модальное окно с таблицей размеров -->
              <button 
                @click="showSizeGuide = true" 
                class="text-xs text-red-600 hover:text-red-700 underline flex items-center gap-1"
              >
                <!-- Иконка справки (SVG) -->
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Таблица размеров
              </button>
            </div>
          
            <!-- Сетка кнопок выбора размера -->
            <div class="grid grid-cols-4 sm:grid-cols-5 gap-2">
              <button
                v-for="size in sortedSizes"
                :key="size"
                @click="selectSize(size)"
                :disabled="!isSizeAvailable(size)"
                :class="[
                  selectedSize === size 
                    ? 'bg-red-600 text-white border-red-600 shadow-md' 
                    : isSizeAvailable(size)
                      ? 'bg-white text-gray-700 border-gray-300 hover:border-red-600 hover:text-red-600'
                      : 'bg-gray-50 text-gray-300 border-gray-100 cursor-not-allowed',
                  'px-3 py-3 text-sm font-bold border rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-red-600 relative'
                ]"
              >
                {{ size }}
                <!-- Бейдж с количеством доступных единиц для этого размера -->
                <span 
                  v-if="getQuantity(size) > 0" 
                  class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-gray-900 text-white text-[10px] rounded-full flex items-center justify-center border-2 border-white"
                >
                  {{ getQuantity(size) }}
                </span>
              </button>
            </div>
          
            <!-- Сообщение об ошибке выбора размера: показывается при валидации -->
            <p v-if="sizeError" class="mt-2 text-sm text-red-600 font-medium animate-pulse">
              {{ sizeError }}
            </p>
            
            <!-- Подсказка об автоматическом обновлении остатков -->
            <p v-if="hasRealSizes && autoRefreshEnabled" class="mt-3 text-xs text-gray-400">
            </p>
          </div>

          <!-- ВАРИАНТ Б: Отображение для товаров без размеров (подвески, серьги, броши) -->
          <div v-else class="mb-8 border-t border-gray-100 pt-6">
            <div class="flex items-center gap-3">
              <!-- Иконка статуса "в наличии" (галочка, зелёная) -->
              <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
              </div>
              <div>
                <p class="text-sm font-bold text-gray-900 uppercase tracking-wide">Доступно к заказу</p>
                <p class="text-lg text-gray-600">
                  В наличии: <span class="font-bold text-gray-900">{{ totalStock }} шт.</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Блок характеристик товара: категория, материал, камень -->
          <div class="border-t border-gray-200 pt-6 mb-8 space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Категория:</span>
              <span class="text-gray-900 font-medium">{{ product.category?.name || '—' }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-500">Материал:</span>
              <span class="text-gray-900 font-medium">{{ product.material?.name || '—' }}</span>
            </div>
            <div v-if="product.stone" class="flex justify-between text-sm">
              <span class="text-gray-500">Камень:</span>
              <span class="text-gray-900 font-medium">{{ product.stone.name }}</span>
            </div>
          </div>

          <!-- Блок кнопок действий: добавление в корзину и переход в корзину -->
          <div class="mt-auto space-y-4">
            <!-- Кнопка добавления в корзину: блокируется если размер не выбран или товара нет -->
            <button
              @click="addToCart"
              :disabled="!canAddToCart || isAdding"
              :class="[
                isAdding ? 'bg-gray-400 cursor-wait' : 'bg-black hover:bg-red-600',
                !canAddToCart ? 'opacity-50 cursor-not-allowed' : ''
              ]"
              class="w-full py-4 text-white font-semibold uppercase tracking-[0.15em] rounded-lg transition-colors flex items-center justify-center gap-2"
            >
              <!-- Индикатор загрузки: спиннер во время добавления -->
              <span v-if="isAdding" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
              {{ isAdding ? 'Добавление...' : (canAddToCart ? 'Добавить в корзину' : 'Выберите размер') }}
            </button>

            <!-- Кнопка перехода в корзину: вторичное действие -->
            <router-link
              to="/cart"
              class="block w-full py-4 text-center border border-gray-300 text-gray-700 font-semibold uppercase tracking-[0.15em] rounded-lg hover:border-black hover:text-black transition-colors"
            >
              Перейти в корзину
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Состояние ошибки: товар не найден по указанному slug -->
    <div v-else class="text-center py-20">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">Товар не найден</h2>
      <router-link to="/catalog" class="text-red-600 hover:text-red-700 underline">
        Вернуться в каталог
      </router-link>
    </div>

   <!-- === МОДАЛЬНОЕ ОКНО: Таблица размеров колец === -->
<div v-if="showSizeGuide" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4" @click="showSizeGuide = false">
  <!-- Контент модального окна: клик внутри не закрывает окно -->
  <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
    <div class="flex justify-between items-center mb-4 p-4 sm:p-6 border-b border-gray-100">
      <h3 class="text-lg sm:text-xl font-bold text-gray-900 uppercase tracking-wide">Таблица размеров колец</h3>
      <!-- Кнопка закрытия модального окна -->
      <button @click="showSizeGuide = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none flex-shrink-0 ml-2">&times;</button>
    </div>
  
    <div class="p-4 sm:p-6 space-y-4 text-sm text-gray-600">
      <!-- Инструкция по измерению размера пальца -->
      <p class="text-xs sm:text-sm"><strong>Как измерить:</strong> оберните полоску бумаги вокруг пальца, отметьте место соединения и измерьте длину в мм. Найдите соответствующий размер в таблице.</p>
    
      <!-- Таблица соответствия размеров: РФ, окружность, диаметр с горизонтальной прокруткой -->
      <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
        <table class="w-full border-collapse min-w-[300px]">
          <thead>
            <tr class="border-b border-gray-200">
              <th class="text-left py-2 sm:py-3 px-2 font-semibold text-gray-900 text-xs sm:text-sm whitespace-nowrap">Размер (РФ)</th>
              <th class="text-left py-2 sm:py-3 px-2 font-semibold text-gray-900 text-xs sm:text-sm whitespace-nowrap">Длина окружности (мм)</th>
              <th class="text-left py-2 sm:py-3 px-2 font-semibold text-gray-900 text-xs sm:text-sm whitespace-nowrap">Диаметр (мм)</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in sizeTable" :key="row.size" class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
              <td class="py-2 sm:py-3 px-2 font-medium text-gray-900 text-xs sm:text-sm whitespace-nowrap">{{ row.size }}</td>
              <td class="py-2 sm:py-3 px-2 text-xs sm:text-sm whitespace-nowrap">{{ row.circumference }}</td>
              <td class="py-2 sm:py-3 px-2 text-xs sm:text-sm whitespace-nowrap">{{ row.diameter }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    
      <!-- Примечание: рекомендация измерять палец вечером -->
      <p class="text-xs text-gray-400 italic">* Измеряйте палец вечером — к концу дня он немного отекает.</p>
    </div>
  
    <!-- Кнопка подтверждения: закрывает модальное окно -->
    <div class="p-4 sm:p-6 border-t border-gray-100">
      <button 
        @click="showSizeGuide = false" 
        class="w-full py-3 sm:py-3.5 bg-red-600 text-white font-semibold uppercase tracking-[0.15em] rounded-lg hover:bg-red-700 transition text-sm sm:text-base"
      >
        Понятно
      </button>
    </div>
  </div>
</div>
</div>
</template>

<script setup>
// === ИМПОРТЫ ЗАВИСИМОСТЕЙ ===

// Хуки реактивности и жизненного цикла Vue 3
import { ref, computed, onMounted, onUnmounted, onActivated } from 'vue';

// Хук маршрутизации для получения параметров из URL (slug товара)
import { useRoute } from 'vue-router';

// Библиотека для отправки HTTP-запросов к API
import axios from 'axios';

// Композиция для управления состоянием корзины покупок
import { useCart } from '../composables/useCart';


// === ИНИЦИАЛИЗАЦИЯ КОМПОЗИТОРА И МАРШРУТИЗАЦИИ ===

// Получение объекта текущего маршрута для доступа к параметрам
const route = useRoute();

// Деструктуризация метода addToCart из композиции корзины
const { addToCart: addCartItem } = useCart();


// === СОСТОЯНИЕ КОМПОНЕНТА (РЕАКТИВНЫЕ ДАННЫЕ) ===

// Данные товара, загруженные с сервера (изначально null)
const product = ref(null);

// Флаги состояния загрузки и взаимодействия с пользователем
const loading = ref(true);           // Индикатор первоначальной загрузки данных товара
const isAdding = ref(false);         // Индикатор процесса добавления товара в корзину
const isRefreshing = ref(false);     // Индикатор фонового обновления данных о наличии

// Состояние выбора размера для товаров с размерной сеткой (кольца)
const selectedSize = ref(null);      // Выбранный пользователем размер кольца
const sizeError = ref('');           // Сообщение об ошибке валидации выбора размера
const showSizeGuide = ref(false);    // Флаг отображения модального окна с таблицей размеров

// Состояние галереи изображений товара
const activeImageIndex = ref(0);     // Индекс текущего отображаемого изображения в галерее

// Состояние автоматического обновления данных о наличии
const autoRefreshEnabled = ref(true);
let refreshInterval = null;          // Идентификатор интервала для последующей очистки


// === СПРАВОЧНЫЕ ДАННЫЕ: ТАБЛИЦА РАЗМЕРОВ КОЛЕЦ ===

// Справочная информация для модального окна "Таблица размеров"
// Содержит соответствие российского размера, длины окружности и диаметра в миллиметрах
const sizeTable = [
  { size: '15.5', circumference: '48-49 мм', diameter: '15.5 мм' },
  { size: '16', circumference: '50-51 мм', diameter: '16.0 мм' },
  { size: '16.5', circumference: '52-53 мм', diameter: '16.5 мм' },
  { size: '17', circumference: '53-54 мм', diameter: '17.0 мм' },
  { size: '17.5', circumference: '55-56 мм', diameter: '17.5 мм' },
  { size: '18', circumference: '56-57 мм', diameter: '18.0 мм' },
  { size: '18.5', circumference: '58-59 мм', diameter: '18.5 мм' },
  { size: '19', circumference: '59-60 мм', diameter: '19.0 мм' },
  { size: '19.5', circumference: '61-62 мм', diameter: '19.5 мм' },
  { size: '20', circumference: '62-63 мм', diameter: '20.0 мм' },
  { size: '20.5', circumference: '64-65 мм', diameter: '20.5 мм' },
  { size: '21', circumference: '65-66 мм', diameter: '21.0 мм' },
  { size: '21.5', circumference: '67-68 мм', diameter: '21.5 мм' },
  { size: '22', circumference: '68-69 мм', diameter: '22.0 мм' },
  { size: '22.5', circumference: '70-71 мм', diameter: '22.5 мм' },
  { size: '23', circumference: '71-72 мм', diameter: '23.0 мм' },
  { size: '23.5', circumference: '73-74 мм', diameter: '23.5 мм' },
  { size: '24', circumference: '74-75 мм', diameter: '24.0 мм' },
];


// === ВЫЧИСЛЯЕМЫЕ СВОЙСТВА ===

// Определение наличия реальной размерной сетки у товара
// Возвращает true, если поле sizes является объектом с ключами (характерно для колец)
const hasRealSizes = computed(() => {
  if (!product.value?.sizes) return false;
  return typeof product.value.sizes === 'object' && Object.keys(product.value.sizes).length > 0;
});

// Расчёт общего количества товара на складе
// Для колец: сумма остатков по всем размерам из JSON-поля sizes
// Для остальных товаров: прямое значение поля stock
const totalStock = computed(() => {
  if (!product.value) return 0;

  if (hasRealSizes.value) {
    return Object.values(product.value.sizes).reduce((sum, qty) => sum + (Number(qty) || 0), 0);
  }
  
  return Number(product.value.stock) || 0;
});

// Сортировка доступных размеров по возрастанию числового значения
// Обеспечивает корректный порядок отображения кнопок выбора размера (16, 16.5, 17...)
const sortedSizes = computed(() => {
  if (!hasRealSizes.value) return [];
  return Object.keys(product.value.sizes).sort((a, b) => parseFloat(a) - parseFloat(b));
});

// Формирование массива URL всех изображений товара с приоритетом полей
// Сначала проверяется массив images, затем поле image как запасной вариант
const allImages = computed(() => {
  if (!product.value) return [];
  const images = [];

  if (product.value.images && Array.isArray(product.value.images)) {
    product.value.images.forEach(img => {
      if (img.url) images.push(img.url);
    });
  }

  if (images.length === 0 && product.value.image) {
    images.push(`/storage/${product.value.image}`);
  }

  return images;
});

// Получение URL текущего активного изображения галереи по индексу
const mainImage = computed(() => {
  return allImages.value[activeImageIndex.value] || '';
});

// Вычисляемое свойство: доступность кнопки "Добавить в корзину"
// Для колец: требуется выбор доступного размера
// Для остальных товаров: достаточно наличия общего остатка > 0
const canAddToCart = computed(() => {
  if (!product.value || totalStock.value <= 0) return false;
  
  if (hasRealSizes.value) {
    return selectedSize.value && isSizeAvailable(selectedSize.value);
  }
  
  return true;
});


// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Форматирование цены в рублях с разделителями тысяч и символом валюты
const formatPrice = (value) => {
  const num = Number(value);
  return isNaN(num) ? '0 ₽' : `${num.toLocaleString('ru-RU')} ₽`;
};

// Проверка доступности конкретного размера для покупки
// Возвращает true если остаток по размеру больше нуля
const isSizeAvailable = (size) => {
  if (!hasRealSizes.value) return false;
  return (product.value.sizes?.[size] || 0) > 0;
};

// Получение количества единиц товара для указанного размера
// Возвращает 0 если размер не найден или товар без размеров
const getQuantity = (size) => {
  if (!hasRealSizes.value) return 0;
  return product.value.sizes?.[size] || 0;
};


// === ОБРАБОТЧИКИ ВЗАИМОДЕЙСТВИЯ С ПОЛЬЗОВАТЕЛЕМ ===

// Обработчик выбора размера: валидация доступности и установка состояния
const selectSize = (size) => {
  // Игнорируем клик по недоступному размеру
  if (!isSizeAvailable(size)) return;
  selectedSize.value = size;
  sizeError.value = '';
};

// Загрузка данных товара с сервера по параметру slug из маршрута
const fetchProduct = async () => {
  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/products/${route.params.slug}`);
    product.value = response.data;
    return true;
  } catch (error) {
    console.error('Ошибка загрузки товара:', error);
    // Обработка ошибки 404: товар не найден
    if (error.response?.status === 404) {
      product.value = null;
    }
    return false;
  }
};

// Первичная загрузка товара при монтировании компонента
const loadProduct = async () => {
  loading.value = true;
  try {
    await fetchProduct();
    // Сброс состояния выбора при загрузке нового товара
    selectedSize.value = null;
    sizeError.value = '';
    activeImageIndex.value = 0;
  } finally {
    loading.value = false;
  }
};

// Ручное обновление данных товара с визуальным индикатором процесса
const refreshProduct = async () => {
  // Защита от повторных вызовов во время выполнения
  if (isRefreshing.value) return;

  isRefreshing.value = true;
  try {
    const success = await fetchProduct();
    if (success) {
      // Сброс выбора размера, если он стал недоступен после обновления остатков
      if (selectedSize.value && !isSizeAvailable(selectedSize.value)) {
        selectedSize.value = null;
        sizeError.value = 'Выбранный размер больше не доступен';
      }
    }
  } finally {
    isRefreshing.value = false;
  }
};

// Настройка периодического автообновления данных о наличии (каждые 30 секунд)
const startAutoRefresh = () => {
  // Очистка предыдущего интервала если он существует
  if (refreshInterval) clearInterval(refreshInterval);

  refreshInterval = setInterval(async () => {
    // Обновление только если включено автообновление и нет активных загрузок
    if (autoRefreshEnabled.value && !loading.value && !isRefreshing.value) {
      await fetchProduct();
    }
  }, 30000);
};

// Остановка автообновления и очистка интервала для предотвращения утечек памяти
const stopAutoRefresh = () => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
};

// Основная функция добавления товара в корзину с полной валидацией
const addToCart = async () => {
  // Валидация выбора размера для товаров с размерной сеткой (кольца)
  if (hasRealSizes.value) {
    if (!selectedSize.value) {
      sizeError.value = 'Пожалуйста, выберите размер';
      return;
    }
    if (!isSizeAvailable(selectedSize.value)) {
      sizeError.value = 'Этого размера нет в наличии';
      // Попытка обновить данные на случай, что остатки изменились
      await refreshProduct();
      return;
    }
  }

  // Защита от добавления товара с нулевым остатком
  if (!product.value || totalStock.value <= 0) return;

  isAdding.value = true;
  sizeError.value = '';

  try {
    // Небольшая задержка для визуальной обратной связи пользователю
    await new Promise(r => setTimeout(r, 200));

    // Определение финального значения размера для передачи в корзину
    // Для колец: строковое значение размера, для остальных: null
    const finalSize = hasRealSizes.value ? String(selectedSize.value) : null;

    // Логирование для отладки: проверка типа и значения размера
    console.log('Отправка в корзину:', {
      productId: product.value.id,
      size: finalSize, 
      sizeType: typeof finalSize
    });

    // Вызов композиции useCart для добавления товара в корзину
    const result = await addCartItem(product.value, 1, finalSize);

if (result?.success) {
  // Уведомление об успешном добавлении с указанием размера если есть
  alert(`Товар добавлен!${finalSize ? ` Размер: ${finalSize}` : ''}`);
  // Сброс выбора размера после успешного добавления
  selectedSize.value = null;
  // Обновление данных товара для актуализации остатков
  await refreshProduct();
} else {
  // Проверка на ошибку авторизации (401)
  if (result?.statusCode === 401 || result?.message === 'Unauthenticated.') {
    alert('Для добавления товара в корзину необходимо войти в систему');
  } else {
    // Отображение сообщения об ошибке от сервера
    alert(result?.message || 'Ошибка при добавлении');
  }
}

} catch (e) {
  console.error(e);
  
  // Обработка ошибки 401 (неавторизован)
  if (e.response?.status === 401 || e.message === 'Unauthenticated.') {
    alert('Для добавления товара в корзину необходимо войти в систему');
  } else {
    // Обработка других непредвиденных ошибок
    alert('Ошибка: ' + (e.message || 'Не удалось добавить товар в корзину'));
  }
} finally {
  isAdding.value = false;
}
};


// === ЖИЗНЕННЫЙ ЦИКЛ КОМПОНЕНТА ===

// Выполняется при первом монтировании компонента в DOM
onMounted(async () => {
  await loadProduct();
  startAutoRefresh();
});

// Выполняется при активации компонента через keep-alive (возврат на страницу)
// Позволяет обновить данные без полной перезагрузки компонента
onActivated(async () => {
  if (product.value) {
    await refreshProduct();
  }
});

// Выполняется при уничтожении компонента: очистка ресурсов
onUnmounted(() => {
  stopAutoRefresh();
});

</script>

<style scoped>
/* ГЛОБАЛЬНЫЕ ПЕРЕХОДЫ И АНИМАЦИИ */
/* Единая настройка плавных переходов для всех интерактивных элементов:
   изменение цвета текста, фона и границ происходит за 0.2 секунды */
* {
  transition: color 0.2s, background-color 0.2s, border-color 0.2s;
}
</style>