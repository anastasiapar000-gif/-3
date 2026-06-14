<template>
  <!-- Корневой контейнер страницы корзины: минимальная высота на весь экран, белый фон -->
  <div class="min-h-screen bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      
      <!-- Заголовок страницы -->
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-12 tracking-tight">
        Корзина
      </h1>

      <!-- Индикатор загрузки: показывается во время получения данных корзины с сервера -->
      <div v-if="loading" class="flex items-center justify-center min-h-[40vh]">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
      </div>

      <!-- Состояние пустой корзины: показывается, если массив товаров пуст -->
      <div v-else-if="items.length === 0" class="text-center py-20">
        <div class="inline-block p-6 rounded-full bg-gray-50 mb-6">
          <!-- Иконка пустой корзины (SVG) -->
          <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
        </div>
        <p class="text-gray-500 text-lg mb-8 font-medium">Ваша корзина пуста</p>
        <router-link 
          to="/catalog" 
          class="inline-block px-8 py-4 bg-black hover:bg-red-600 text-white text-sm font-semibold uppercase tracking-wider transition-colors rounded-lg"
        >
          Перейти в каталог
        </router-link>
      </div>

      <!-- Список товаров в корзине: показывается при наличии товаров -->
      <div v-else>
        <!-- Заголовки таблицы: видны только на десктопе (скрыты на мобильных) -->
        <div class="hidden sm:grid sm:grid-cols-12 gap-4 pb-4 border-b border-gray-200 text-xs text-gray-500 font-medium uppercase tracking-wide">
          <div class="col-span-5">Товар</div>
          <div class="col-span-2 text-center">Цена</div>
          <div class="col-span-2 text-center">Количество</div>
          <div class="col-span-2 text-center">Итого</div>
          <div class="col-span-1"></div>
        </div>

        <!-- Список позиций корзины -->
        <div class="py-6 space-y-6">
          <div 
            v-for="item in items" 
            :key="item.id" 
            class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-start sm:items-center py-6 border-b border-gray-100 last:border-b-0"
          >
            
            <!-- Блок информации о товаре: изображение + название + размер -->
            <div class="sm:col-span-5 flex gap-4">
              <!-- Контейнер изображения товара -->
              <div class="w-24 h-24 bg-gray-50 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200">
                <!-- Изображение товара: показывается если есть валидный URL -->
                <img 
                  v-if="getImageUrl(item.image)" 
                  :src="getImageUrl(item.image)" 
                  :alt="item.name"
                  class="w-full h-full object-cover"
                  @error="onImageError"
                >
                <!-- Заглушка: показывается если изображение не загрузилось или отсутствует -->
                <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                  <!-- Иконка заглушки (отсутствующее изображение, SVG) -->
                  <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              
              <!-- Текстовая информация о товаре -->
              <div class="flex-1 min-w-0 pt-1">
                <h3 class="font-semibold text-gray-900 text-base truncate">{{ item.name }}</h3>
                
                <!-- Отображение размера товара: для колец или товаров без размера -->
                <div class="mt-1">
                  <p v-if="item.size !== null && item.size !== undefined && String(item.size).trim() !== ''" 
                     class="text-sm text-gray-500">
                    Размер: <span class="font-medium text-gray-700">{{ String(item.size).trim() }}</span>
                  </p>
                  <p v-else class="text-sm text-gray-400 italic">
                    Без размера
                  </p>
                </div>
              </div>
            </div>

            <!-- Цена за единицу товара -->
            <div class="sm:col-span-2 text-center text-sm font-medium text-gray-700 pt-2 sm:pt-0">
              <span class="sm:hidden text-gray-500 text-xs mr-2">Цена:</span>
              {{ formatPrice(item.price) }}
            </div>

            <!-- Управление количеством товара: кнопки +/- с проверкой лимитов -->
            <div class="sm:col-span-2 flex items-center justify-center pt-2 sm:pt-0">
              <span class="sm:hidden text-gray-500 text-xs mr-2">Кол-во:</span>
              <div class="flex items-center border border-gray-300 rounded-lg">
                <!-- Кнопка уменьшения: отключена если количество = 1 -->
                <button 
                  @click="decrementQuantity(item)"
                  :disabled="item.quantity <= 1"
                  class="w-9 h-9 flex items-center justify-center text-gray-600 hover:text-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                >
                  −
                </button>
                <!-- Отображение текущего количества -->
                <span class="w-9 h-9 flex items-center justify-center text-gray-900 text-sm border-x border-gray-300 font-medium">
                  {{ item.quantity }}
                </span>
                <!-- Кнопка увеличения: отключена если достигнуто максимальное доступное количество -->
                <button 
                  @click="incrementQuantity(item)"
                  :disabled="item.quantity >= getMaxQuantity(item)"
                  class="w-9 h-9 flex items-center justify-center text-gray-600 hover:text-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                  :title="item.quantity >= getMaxQuantity(item) ? `Доступно только ${getMaxQuantity(item)} шт.` : ''"
                >
                  +
                </button>
              </div>
              <!-- Подсказка о максимальном количестве (только на десктопе) -->
              <span v-if="getMaxQuantity(item) < 999" class="ml-2 text-xs text-gray-500 hidden sm:inline">
                (макс. {{ getMaxQuantity(item) }})
              </span>
            </div>

            <!-- Итоговая стоимость позиции: цена * количество -->
            <div class="sm:col-span-2 text-center text-sm font-semibold text-gray-900 pt-2 sm:pt-0">
              <span class="sm:hidden text-gray-500 text-xs mr-2">Итого:</span>
              {{ formatPrice(item.price * item.quantity) }}
            </div>

            <!-- Кнопка удаления товара из корзины -->
            <div class="sm:col-span-1 flex justify-center pt-2 sm:pt-0">
              <button 
                @click="removeFromCart(item.id)" 
                class="w-8 h-8 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium rounded-lg transition-colors"
                title="Удалить из корзины"
              >
                <!-- Иконка удаления (крестик, SVG) -->
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Разделитель перед итоговой секцией -->
        <div class="border-t border-gray-200 my-8"></div>

        <!-- Блок итоговой суммы и количества товаров -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-12">
          <div>
            <p class="text-sm text-gray-500 font-medium mb-1">Общая сумма:</p>
            <p class="text-3xl font-bold text-red-600">{{ formatPrice(totalPrice) }}</p>
          </div>
          <p class="text-sm text-gray-500 font-medium">
            Товаров: {{ totalItems }}
          </p>
        </div>

        <!-- Кнопка перехода к оформлению заказа -->
        <div class="flex justify-center">
          <router-link 
            to="/checkout" 
            class="px-16 py-4 bg-black hover:bg-red-600 text-white text-sm font-semibold uppercase tracking-wider rounded-lg transition-colors shadow-lg shadow-red-100"
          >
            Оформить заказ
          </router-link>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
// Импорт composable-функции useCart для управления состоянием корзины
// Функция предоставляет реактивные данные и методы для работы с корзиной
import { useCart } from '../composables/useCart';

// Деструктуризация возвращаемых значений из useCart:
// items - массив товаров в корзине
// loading - флаг загрузки данных
// removeFromCart - метод удаления товара
// updateQuantity - метод обновления количества
// totalPrice - вычисляемая общая сумма
// totalItems - вычисляемое общее количество единиц
const { items, loading, removeFromCart, updateQuantity, totalPrice, totalItems } = useCart();

// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Получение максимального доступного количества для позиции корзины
// Учитывает остатки товара: для колец — по конкретному размеру, для остальных — общий остаток
const getMaxQuantity = (item) => {
  // Если в позиции есть полный объект продукта с данными об остатках
  if (item.product) {
    // Для товаров с размерами (кольца): проверяем остаток по конкретному размеру
    if (item.product.sizes && item.size !== null && item.size !== undefined) {
      const sizeKey = String(item.size);
      const sizeQty = item.product.sizes[sizeKey];
      // Если остаток по размеру определён — возвращаем его, иначе 0
      if (sizeQty !== undefined && sizeQty !== null) {
        return Math.max(0, Number(sizeQty));
      }
      return 0;
    }
    // Для товаров без размеров: используем общий остаток stock
    if (item.product.stock !== undefined && item.product.stock !== null) {
      return Math.max(0, Number(item.product.stock));
    }
  }
  // Если данные об остатках недоступны — возвращаем большое число (фактически без лимита)
  return 999;
};

// Увеличение количества товара на 1 с проверкой лимита доступности
const incrementQuantity = (item) => {
  const maxQty = getMaxQuantity(item);
  // Увеличиваем только если текущее количество меньше максимального доступного
  if (item.quantity < maxQty) {
    updateQuantity(item.id, item.quantity + 1);
  }
};

// Уменьшение количества товара на 1 (не меньше 1)
const decrementQuantity = (item) => {
  // Уменьшаем только если текущее количество больше 1
  if (item.quantity > 1) {
    updateQuantity(item.id, item.quantity - 1);
  }
};

// Форматирование цены: преобразование числа в строку с разделителями тысяч и символом рубля
const formatPrice = (value) => {
  const num = Number(value);
  // Если значение не является числом — возвращаем "0 ₽"
  return isNaN(num) ? '0 ₽' : `${num.toLocaleString('ru-RU')} ₽`;
};

// Получение полного абсолютного URL для изображения товара
// Обрабатывает разные форматы путей: относительные, полные, с префиксом /storage/
const getImageUrl = (image) => {
  // Если изображения нет — возвращаем пустую строку
  if (!image) return '';
  
  // Если это уже полный URL (начинается с http/https) — возвращаем как есть
  if (image.startsWith('http://') || image.startsWith('https://')) {
    return image;
  }
  
  // Если путь уже содержит /storage/ (относительный путь от корня сайта)
  if (image.startsWith('/storage/')) {
    return `http://127.0.0.1:8000${image}`;
  }
  
  // Если это просто путь к файлу без префикса (products/...) — добавляем /storage/
  if (!image.startsWith('storage/') && !image.startsWith('/')) {
    return `http://127.0.0.1:8000/storage/${image}`;
  }
  
  // Стандартный случай: путь относительно корня сайта
  return `http://127.0.0.1:8000${image.startsWith('/') ? image : '/' + image}`;
};

// Обработчик ошибки загрузки изображения: скрывает битую картинку
const onImageError = (event) => {
  console.warn('Не удалось загрузить изображение:', event.target.src);
  // Скрываем элемент изображения, чтобы показать заглушку
  event.target.style.display = 'none';
};
</script>