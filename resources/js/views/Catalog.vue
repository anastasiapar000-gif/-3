<template>
  <div class="min-h-screen bg-white">
    
    <!-- Заголовок с фоном -->
    <div class="relative border-b border-gray-800 overflow-hidden">
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://i.pinimg.com/736x/df/62/1e/df621eb33fbe80474164151ab0e02d79.jpg');"
      >
        <div class="absolute inset-0 bg-black/50"></div>
      </div>

      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-5xl font-bold text-red-600 mb-4 tracking-[0.2em]">КАТАЛОГ</h1>
        </div>
      </div>
    </div>

    <!-- Основной контент -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="flex flex-col lg:flex-row gap-12">
        
        <!-- Левая панель -->
        <aside class="lg:w-56 flex-shrink-0">
          <div class="sticky top-24 space-y-10">
            
            <!-- Категории -->
            <div>
              <h3 class="text-xs font-bold text-gray-900 uppercase tracking-[0.2em] mb-4">Категории</h3>
              <div class="space-y-3">
                <button 
                  @click="selectCategory(null)"
                  :class="filters.category === null ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-gray-900'"
                  class="block text-left text-sm tracking-wide transition-colors py-1"
                >
                  Все товары
                </button>
                <button 
                  v-for="cat in categories" 
                  :key="cat.id"
                  @click="selectCategory(cat.id)"
                  :class="filters.category === cat.id ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-gray-900'"
                  class="block text-left text-sm tracking-wide transition-colors py-1"
                >
                  {{ cat.name }}
                </button>
              </div>
            </div>

            <!-- Фильтры -->
            <div class="border-t border-gray-200 pt-8">
              <h3 class="text-xs font-bold text-gray-900 uppercase tracking-[0.2em] mb-4">Фильтры</h3>
              
              <!-- Поиск -->
              <div class="mb-6">
                <input 
                  v-model="filters.search"
                  @input="applyFilters"
                  type="text"
                  placeholder="Поиск..."
                  class="w-full px-3 py-2 text-sm border border-gray-300 focus:ring-1 focus:ring-red-500 focus:border-red-500 transition placeholder-gray-400"
                >
              </div>

              <!-- Поля ввода цены вместо слайдера -->
              <div class="mb-6">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-[0.2em] mb-3">
                  Цена, ₽
                </label>
                
                <div class="flex items-center gap-2">
                  <!-- Поле "От" -->
                  <div class="relative w-1/2">
                    <input 
                      v-model.number="filters.minPrice"
                      @change="applyFilters"
                      type="number"
                      :min="priceRange.min"
                      :max="filters.maxPrice"
                      placeholder="От"
                      class="w-full pl-3 pr-8 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500 focus:border-red-500 transition placeholder-gray-400 appearance-none"
                    >
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">₽</span>
                  </div>

                  <span class="text-gray-400">—</span>

                  <!-- Поле "До" -->
                  <div class="relative w-1/2">
                    <input 
                      v-model.number="filters.maxPrice"
                      @change="applyFilters"
                      type="number"
                      :min="filters.minPrice"
                      :max="priceRange.max"
                      placeholder="До"
                      class="w-full pl-3 pr-8 py-2 text-sm border border-gray-300 rounded-md focus:ring-1 focus:ring-red-500 focus:border-red-500 transition placeholder-gray-400 appearance-none"
                    >
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none">₽</span>
                  </div>
                </div>
              </div>

              <!-- Сбросить -->
              <button 
                @click="resetFilters"
                class="w-full px-4 py-3 bg-red-600 text-white text-xs font-semibold uppercase tracking-[0.15em] rounded-lg hover:bg-red-700 transition-colors"
              >
                Сбросить фильтры
              </button>
            </div>
          </div>
        </aside>

        <!-- Основной контент -->
        <main class="flex-1">
          <!-- Сортировка -->
          <div class="flex justify-between items-center mb-10 pb-6 border-b border-gray-200">
            <p class="text-sm text-gray-500">
              Найдено: <span class="font-semibold text-gray-900">{{ safeTotal }}</span>
            </p>
            <select 
              v-model="sortBy"
              @change="applyFilters"
              class="px-4 py-2 text-sm border border-gray-300 focus:ring-1 focus:ring-red-500 focus:border-red-500"
            >
              <option value="newest">Сначала новые</option>
              <option value="price_asc">Цена: по возрастанию</option>
              <option value="price_desc">Цена: по убыванию</option>
            </select>
          </div>

          <!-- Загрузка -->
          <div v-if="loading" class="text-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-2 border-red-600 border-t-transparent mx-auto"></div>
          </div>

          <!-- Пусто -->
          <div v-else-if="productsList.length === 0" class="text-center py-20">
            <p class="text-gray-500 mb-4">Украшения не найдены</p>
            <button @click="resetFilters" class="text-xs text-red-600 hover:text-red-700 uppercase tracking-[0.15em]">
              Сбросить фильтры
            </button>
          </div>

          <!-- Сетка товаров с АНИМАЦИЕЙ -->
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            <div 
              v-for="(product, index) in productsList" 
              :key="product.id" 
              class="group animate-fade-in-up"
              :style="{ animationDelay: `${index * 50}ms` }"
            >
              <div class="relative aspect-square overflow-hidden bg-gray-50 mb-4">
                <img 
                  v-if="getImageUrl(product)"
                  :src="getImageUrl(product)" 
                  :alt="product.name"
                  class="w-full h-full object-cover group-hover:opacity-90 transition duration-300"
                  @error="handleImageError($event)"
                >
                <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                  <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                
                <div v-if="product.stock === 0" class="absolute top-3 left-3 px-3 py-1.5 bg-white/95 text-red-600 text-[10px] font-bold uppercase tracking-wider rounded">
                  Нет в наличии
                </div>
              </div>

              <div>
                <h3 class="font-semibold text-gray-900 text-sm mb-2 group-hover:text-red-600 transition-colors">
                  {{ product.name }}
                </h3>
               <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ product.description }}</p>
<div class="flex items-center justify-between">
                  <p class="text-lg font-bold text-gray-900">{{ formatPrice(product.price) }}</p>
                  <router-link :to="`/product/${product.slug}`" class="text-xs text-gray-500 hover:text-red-600 uppercase tracking-[0.15em]">
                    Подробнее
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Пагинация -->
          <div v-if="safeLastPage > 1" class="flex justify-center gap-3 mt-16">
            <button 
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-4 py-2 text-xs text-gray-500 hover:text-gray-900 disabled:opacity-50 uppercase tracking-[0.15em]"
            >
              ← Назад
            </button>
            
            <template v-for="page in safePaginationPages" :key="page">
              <button 
                v-if="typeof page === 'number'"
                @click="goToPage(page)"
                :class="page === currentPage ? 'text-red-600 font-semibold' : 'text-gray-500 hover:text-gray-900'"
                class="px-3 py-2 text-xs uppercase tracking-[0.15em]"
              >
                {{ page }}
              </button>
              <span v-else class="px-2 text-gray-300">...</span>
            </template>
            
            <button 
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === safeLastPage"
              class="px-4 py-2 text-xs text-gray-500 hover:text-gray-900 disabled:opacity-50 uppercase tracking-[0.15em]"
            >
              Вперёд →
            </button>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// ============================================================================
// СОСТОЯНИЕ КОМПОНЕНТА (РЕАКТИВНЫЕ ДАННЫЕ)
// ============================================================================

// Данные каталога: список товаров, пагинация, общее количество
const products = ref({ data: [], current_page: 1, last_page: 1, total: 0 });

// Список категорий для фильтрации и отображения в боковой панели
const categories = ref([]);

// Флаг загрузки данных для отображения индикатора
const loading = ref(true);

// Текущий способ сортировки товаров (по умолчанию: новые)
const sortBy = ref('newest');

// Текущая страница пагинации
const currentPage = ref(1);

// Флаг инициализации диапазона цен (чтобы не пересчитывать при каждом запросе)
const priceRangeInitialized = ref(false);

// Диапазон цен, вычисленный на основе загруженных товаров
const priceRange = ref({ min: 0, max: 100000 });

// Активные фильтры: категория, ценовой диапазон, поисковый запрос
const filters = ref({
  category: null,
  minPrice: 0,
  maxPrice: 100000,
  search: ''
});

// ============================================================================
// ВЫЧИСЛЯЕМЫЕ СВОЙСТВА
// ============================================================================

// Безопасное получение списка товаров с проверкой типа данных
const productsList = computed(() => Array.isArray(products.value.data) ? products.value.data : []);

// Безопасное получение последней страницы пагинации с валидацией диапазона
const safeLastPage = computed(() => {
  const lp = Number(products.value.last_page);
  return (lp >= 1 && lp <= 1000) ? lp : 1;
});

// Безопасное получение общего количества товаров с валидацией
const safeTotal = computed(() => {
  const t = Number(products.value.total);
  return (t >= 0) ? t : 0;
});

// Формирование умной пагинации: показывает первые/последние страницы и текущий диапазон
const safePaginationPages = computed(() => {
  const total = safeLastPage.value;
  const current = currentPage.value;
  
  if (total <= 1 || total > 1000) return [];
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  
  const pages = [1];
  if (current <= 3) {
    pages.push(2, 3, 4, '...', total);
  } else if (current >= total - 2) {
    pages.push('...', total - 3, total - 2, total - 1, total);
  } else {
    pages.push('...', current - 1, current, current + 1, '...', total);
  }
  return pages;
});

// ============================================================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ============================================================================

// Форматирование цены в рублях с разделителями тысяч
const formatPrice = (value) => {
  const num = Number(value);
  return isNaN(num) ? '0 ₽' : `${num.toLocaleString('ru-RU')} ₽`;
};

// Получение URL изображения товара с приоритетом полей и резервным вариантом
const getImageUrl = (product) => {
  if (product.image_url) return product.image_url;
  if (product.images && Array.isArray(product.images) && product.images.length > 0) {
    return product.images[0].url || null;
  }
  if (product.image) return `/storage/${product.image}`;
  return null;
};

// Обработчик ошибки загрузки изображения: скрывает битый элемент
const handleImageError = (event) => {
  event.target.style.display = 'none';
};

// ============================================================================
// ДЕЙСТВИЯ И МЕТОДЫ
// ============================================================================

// Выбор категории: обновление фильтра и перезагрузка данных
const selectCategory = (categoryId) => {
  filters.value.category = categoryId;
  applyFilters();
};

// Автоматический расчёт диапазона цен на основе загруженных товаров
// Выполняется однократно при первой загрузке данных
const calculatePriceRange = (productsData) => {
  if (priceRangeInitialized.value) return;
  
  if (!productsData || !Array.isArray(productsData) || productsData.length === 0) {
    priceRange.value = { min: 0, max: 100000 };
    return;
  }
  
  const prices = productsData.map(p => Number(p.price)).filter(p => !isNaN(p) && p > 0);
  if (prices.length === 0) {
    priceRange.value = { min: 0, max: 100000 };
    return;
  }
  
  const min = Math.min(...prices);
  const max = Math.max(...prices);
  
  // Округление до сотен для удобства отображения
  priceRange.value = {
    min: Math.floor(min / 100) * 100,
    max: Math.ceil(max / 100) * 100
  };
  
  // Синхронизация начальных значений фильтров с вычисленным диапазоном
  filters.value.minPrice = priceRange.value.min;
  filters.value.maxPrice = priceRange.value.max;
  
  priceRangeInitialized.value = true;
};

// Загрузка товаров с сервера: формирование параметров запроса, обработка ответа
const loadData = async (page = 1) => {
  loading.value = true;
  
  try {
    const params = { page, per_page: 12 };
    
    // Применение сортировки
    if (sortBy.value === 'price_asc') params.sort = 'price_asc';
    else if (sortBy.value === 'price_desc') params.sort = 'price_desc';
    
    // Применение фильтра по категории
    if (filters.value.category) params.category_id = filters.value.category;
    
    // Применение фильтра по цене: отправляем только если значения отличаются от дефолтных
    if (filters.value.minPrice > priceRange.value.min || filters.value.maxPrice < priceRange.value.max) {
      params.min_price = filters.value.minPrice;
      params.max_price = filters.value.maxPrice;
    }
    
    // Применение поискового запроса
    if (filters.value.search) params.search = filters.value.search;

    const res = await axios.get('http://127.0.0.1:8000/api/products', { 
      params,
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    });
    
    // Обработка различных форматов ответа от API
    if (res.data && typeof res.data === 'object' && Array.isArray(res.data.data)) {
      products.value = res.data;
      currentPage.value = Number(res.data.current_page) || 1;
      if (!priceRangeInitialized.value) calculatePriceRange(res.data.data);
    } 
    else if (Array.isArray(res.data)) {
      products.value = { data: res.data, current_page: 1, last_page: 1, total: res.data.length };
      currentPage.value = 1;
      if (!priceRangeInitialized.value) calculatePriceRange(res.data);
    } 
    else {
      products.value = { data: [], current_page: 1, last_page: 1, total: 0 };
    }
    
  } catch (error) {
    console.error('Ошибка загрузки товаров:', error);
    products.value = { data: [], current_page: 1, last_page: 1, total: 0 };
  } finally {
    loading.value = false;
  }
};

// Загрузка списка категорий для отображения в фильтре
const loadCategories = async () => {
  try {
    const res = await axios.get('http://127.0.0.1:8000/api/categories');
    categories.value = Array.isArray(res.data) ? res.data : [];
  } catch (error) {
    console.error('Ошибка загрузки категорий:', error);
    categories.value = [];
  }
};

// Применение активных фильтров: сброс пагинации и перезагрузка данных
const applyFilters = () => {
  // Валидация: минимальная цена не может превышать максимальную
  if (filters.value.minPrice > filters.value.maxPrice) {
    filters.value.minPrice = filters.value.maxPrice;
  }
  currentPage.value = 1;
  loadData(1);
};

// Сброс всех фильтров к значениям по умолчанию
const resetFilters = () => {
  filters.value = { 
    category: null, 
    minPrice: priceRange.value.min,
    maxPrice: priceRange.value.max,
    search: '' 
  };
  sortBy.value = 'newest';
  currentPage.value = 1;
  loadData(1);
};

// Переход на указанную страницу пагинации с плавной прокруткой вверх
const goToPage = (page) => {
  if (page < 1 || page > safeLastPage.value) return;
  loadData(page);
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

// ============================================================================
// ЖИЗНЕННЫЙ ЦИКЛ КОМПОНЕНТА
// ============================================================================

// Инициализация: загрузка категорий и товаров при монтировании компонента
onMounted(() => {
  loadCategories();
  loadData(1);
});
</script>

<style scoped>
/* Анимация появления карточек товаров: плавное появление со смещением вверх */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
  opacity: 0;
}

/* Ограничение текста описания товара двумя строками с многоточием */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Плавный переход для интерактивных элементов */
* {
  transition: border-color 0.2s, color 0.2s;
}

/* Скрытие стандартных стрелок у полей ввода числа для кастомного оформления */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield; 
}
</style>