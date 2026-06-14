<template>
  <!-- Корневой контейнер страницы управления заказами -->
  <div>
    
    <!-- Заголовок страницы и панель управления: фильтры и действия -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Заказы</h1>
      <div class="flex flex-wrap gap-3">
        
        <!-- Фильтр по статусу заказа: позволяет отобразить только заказы с выбранным статусом -->
        <select 
          v-model="statusFilter" 
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none text-sm"
        >
          <option value="">Все статусы</option>
          <option value="pending">Новые</option>
          <option value="confirmed">Подтвержденные</option>
          <option value="processing">В обработке</option>
          <option value="shipped">Отправленные</option>
          <option value="delivered">Доставленные</option>
          <option value="cancelled">Отмененные</option>
        </select>
        
        <!-- Кнопка скачивания отчёта в формате Excel: отправляет запрос на сервер для генерации файла -->
        <button 
          @click="downloadOrdersExcel" 
          :disabled="loading"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition font-medium text-sm"
          title="Скачать отчёт по заказам в формате Excel (.xlsx)"
        >
          <!-- Иконка Excel (SVG) -->
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Скачать Excel
        </button>
      </div>
    </div>

    <!-- Индикатор загрузки: показывается во время выполнения асинхронного запроса к серверу -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
      <p class="mt-3 text-gray-500 text-sm">Загрузка заказов...</p>
    </div>

    <!-- Сообщение об ошибке: показывается при неудачном запросе к API -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ error }}
    </div>

    <!-- Список заказов: карточки с основной информацией о каждом заказе -->
    <div v-else class="space-y-4">
      <div 
        v-for="order in filteredOrders" 
        :key="order.id" 
        class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition border border-gray-100"
      >
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
          
          <!-- Левая часть карточки: информация о заказе и клиенте -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-3">
              <h3 class="text-lg font-bold text-gray-900 truncate">
                Заказ №{{ order.order_number || order.id }}
              </h3>
              <span class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</span>
            </div>
            
            <div class="text-sm text-gray-600 mb-3 space-y-1">
              <p><span class="font-medium">Клиент:</span> {{ order.customer_name || 'Гость' }}</p>
              <p v-if="order.user_email"><span class="font-medium">Email:</span> {{ order.user_email }}</p>
              <p v-if="order.address"><span class="font-medium">Адрес:</span> {{ order.address }}</p>
            </div>
          </div>

          <!-- Правая часть карточки: статус, сумма и действия -->
          <div class="lg:w-64 space-y-3 flex-shrink-0">
            <!-- Выпадающий список для смены статуса заказа -->
            <div>
              <label class="block text-xs font-medium text-gray-500 mb-1">Статус</label>
              <select 
                :value="order.status" 
                @change="updateStatus(order.id, $event.target.value)"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 focus:outline-none"
              >
                <option value="pending">Новый</option>
                <option value="confirmed">Подтвержден</option>
                <option value="processing">В обработке</option>
                <option value="shipped">Отправлен</option>
                <option value="delivered">Доставлен</option>
                <option value="cancelled">Отменен</option>
              </select>
            </div>
            
            <!-- Сумма заказа с форматированием -->
            <div class="text-right">
              <p class="text-2xl font-bold text-gray-900">{{ formatPrice(order.total_amount || order.total) }}</p>
            </div>
            
            <!-- Кнопки действий: просмотр деталей и удаление -->
            <div class="flex gap-2">
              <button 
                @click="loadOrderDetails(order.id)"
                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium"
              >
                Просмотр
              </button>
              <button 
                @click="deleteOrder(order.id)"
                class="flex-1 px-3 py-2 text-sm text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition font-medium"
              >
                Удалить
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Сообщение при отсутствии заказов после фильтрации -->
      <div v-if="filteredOrders.length === 0" class="text-center py-12 bg-white rounded-xl shadow border border-gray-100">
        <p class="text-gray-500">Заказы не найдены</p>
      </div>
    </div>

    <!-- Модальное окно: просмотр детальной информации о заказе -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click.self="selectedOrder = null">
      <div class="bg-white rounded-xl max-w-5xl w-full max-h-[90vh] overflow-y-auto shadow-2xl">
        
        <!-- Заголовок модального окна с кнопкой закрытия -->
        <div class="p-6 border-b border-gray-200 flex justify-between items-center sticky top-0 bg-white z-10">
          <h2 class="text-2xl font-bold text-gray-900">
            Заказ №{{ orderDetails?.order?.order_number || orderDetails?.order?.id }}
          </h2>
          <button 
            @click="selectedOrder = null" 
            class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition"
            aria-label="Закрыть"
          >
            <!-- Иконка закрытия (крестик, SVG) -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Индикатор загрузки деталей заказа -->
        <div v-if="loadingDetails" class="p-12 text-center">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
          <p class="mt-3 text-gray-500 text-sm">Загрузка деталей...</p>
        </div>

        <!-- Контент деталей заказа: показывается после успешной загрузки -->
        <div v-else-if="orderDetails" class="p-6 space-y-6">
          
          <!-- Блок 1: Информация о клиенте и заказе (две колонки) -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Карточка: Информация о клиенте -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
              <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                <!-- Иконка пользователя (SVG) -->
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Информация о клиенте
              </h3>
              <div class="space-y-3 text-sm">
                <div>
                  <span class="text-gray-500 block mb-1">ФИО / Имя:</span>
                  <p class="font-semibold text-gray-900 text-base">{{ orderDetails.order.customer_name || 'Не указано' }}</p>
                </div>
                <div v-if="orderDetails.order.user_email">
                  <span class="text-gray-500 block mb-1">Email:</span>
                  <p class="font-medium text-gray-900">{{ orderDetails.order.user_email }}</p>
                </div>
                <div v-if="orderDetails.order.user_phone">
                  <span class="text-gray-500 block mb-1">Телефон:</span>
                  <p class="font-medium text-gray-900">{{ orderDetails.order.user_phone }}</p>
                </div>
              </div>
            </div>
            
            <!-- Карточка: Детали заказа -->
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
              <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                <!-- Иконка заказа (SVG) -->
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Детали заказа
              </h3>
              <div class="space-y-3 text-sm">
                <div>
                  <span class="text-gray-500 block mb-1">Дата оформления:</span>
                  <p class="font-medium text-gray-900">{{ formatDate(orderDetails.order.created_at) }}</p>
                </div>
                <div>
                  <span class="text-gray-500 block mb-1">Статус:</span>
                  <!-- Бейдж статуса с динамическим классом для цвета -->
                  <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide" :class="getStatusClass(orderDetails.order.status)">
                    {{ getStatusText(orderDetails.order.status) }}
                  </span>
                </div>
                <div v-if="orderDetails.order.payment_method">
                  <span class="text-gray-500 block mb-1">Способ оплаты:</span>
                  <p class="font-medium text-gray-900 flex items-center gap-2">
                    <!-- Цветной индикатор способа оплаты -->
                    <span v-if="orderDetails.order.payment_method === 'card'" class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span v-else-if="orderDetails.order.payment_method === 'cash'" class="w-2 h-2 rounded-full bg-green-500"></span>
                    <span v-else-if="orderDetails.order.payment_method === 'sbp'" class="w-2 h-2 rounded-full bg-purple-500"></span>
                    {{ getPaymentMethod(orderDetails.order.payment_method) }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Блок 2: Информация о доставке -->
          <div class="bg-gray-50 p-5 rounded-lg border border-gray-200">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
              <!-- Иконка доставки (грузовик, SVG) -->
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              Доставка
            </h3>
            
            <div class="space-y-3 text-sm">
              <div>
                <span class="text-gray-500 block mb-1">Способ доставки:</span>
                <p class="font-semibold text-gray-900 flex items-center gap-2">
                  {{ orderDetails.delivery?.method_name }}
                  <!-- Отображение стоимости доставки или пометка "Бесплатно" -->
                  <span v-if="orderDetails.delivery?.price > 0" class="text-red-600 font-medium">
                    +{{ orderDetails.delivery?.price_formatted }}
                  </span>
                  <span v-else class="text-green-600 font-medium">
                    Бесплатно
                  </span>
                </p>
              </div>
              
              <!-- Детали доставки для СДЭК -->
              <div v-if="orderDetails.delivery?.method === 'cdek'" class="pl-4 border-l-2 border-blue-200">
                <p class="text-gray-500 mb-1">Пункт выдачи:</p>
                <p class="font-medium text-gray-900">{{ orderDetails.delivery?.details?.city }}</p>
                <p class="text-gray-700">{{ orderDetails.delivery?.details?.address }}</p>
                <p class="text-xs text-gray-500 mt-2 italic">{{ orderDetails.delivery?.details?.note }}</p>
              </div>
              
              <!-- Детали доставки для Иркутска (курьер) -->
              <div v-else-if="orderDetails.delivery?.method === 'irkutsk'" class="pl-4 border-l-2 border-green-200">
                <p class="text-gray-500 mb-1">Адрес доставки:</p>
                <p class="font-medium text-gray-900">{{ orderDetails.delivery?.details?.address }}</p>
                <div v-if="orderDetails.delivery?.details?.entrance" class="text-gray-700 mt-1 grid grid-cols-2 gap-2">
                  <span>Подъезд: {{ orderDetails.delivery?.details?.entrance }}</span>
                  <span>Этаж: {{ orderDetails.delivery?.details?.floor }}</span>
                </div>
                <p v-if="orderDetails.delivery?.details?.intercom" class="text-gray-700">
                  Домофон: {{ orderDetails.delivery?.details?.intercom }}
                </p>
              </div>
              
              <!-- Детали для самовывоза -->
              <div v-else-if="orderDetails.delivery?.method === 'pickup'" class="pl-4 border-l-2 border-purple-200">
                <p class="text-gray-500 mb-1">Адрес самовывоза:</p>
                <p class="font-medium text-gray-900">{{ orderDetails.delivery?.details?.address }}</p>
                <p class="text-gray-700">{{ orderDetails.delivery?.details?.schedule }}</p>
                <p class="text-xs text-green-600 mt-2 font-medium">{{ orderDetails.delivery?.details?.note }}</p>
              </div>
            </div>
          </div>

          <!-- Блок 3: Список товаров в заказе -->
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Товары в заказе</h3>
            <div v-if="orderDetails.items && orderDetails.items.length" class="space-y-3">
              <div 
                v-for="item in orderDetails.items" 
                :key="item.id" 
                class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200"
              >
                <!-- Изображение товара -->
                <div class="w-full sm:w-24 h-24 bg-gray-200 rounded-lg flex-shrink-0 overflow-hidden">
                  <img 
                    v-if="item.product_image" 
                    :src="`/storage/${item.product_image}`" 
                    :alt="item.product_name"
                    class="w-full h-full object-cover"
                    loading="lazy"
                  >
                  <div v-else class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                    Нет фото
                  </div>
                </div>

                <!-- Информация о товаре: название, размер, камень, цена -->
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-gray-900 text-lg">{{ item.product_name }}</h4>
                  
                  <!-- Бейджи размера и камня (если есть) -->
                  <div class="flex flex-wrap gap-2 mt-2 text-sm text-gray-600">
                    <span v-if="item.size" class="px-2 py-1 bg-white rounded border border-gray-300">
                      Размер: <span class="font-medium text-gray-900">{{ item.size }}</span>
                    </span>
                    <span v-if="item.stone?.name" class="px-2 py-1 bg-white rounded border border-gray-300">
                      Камень: <span class="font-medium text-gray-900">{{ item.stone.name }}</span>
                    </span>
                  </div>

                  <!-- Количество, цена за единицу и итоговая сумма по позиции -->
                  <div class="flex flex-wrap items-center gap-4 sm:gap-6 mt-3">
                    <div class="text-sm">
                      <span class="text-gray-500">Количество:</span>
                      <span class="ml-2 font-medium text-gray-900">{{ item.quantity }} шт.</span>
                    </div>
                    <div class="text-sm">
                      <span class="text-gray-500">Цена за шт.:</span>
                      <span class="ml-2 font-medium text-gray-900">{{ formatPrice(item.price) }}</span>
                    </div>
                    <div class="ml-auto text-right">
                      <p class="text-sm text-gray-500">Итого:</p>
                      <p class="text-xl font-bold text-red-600">{{ formatPrice(item.price * item.quantity) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Сообщение при отсутствии товаров в заказе -->
            <div v-else class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-gray-200">
              Товары не найдены
            </div>
          </div>

          <!-- Итоговая секция: общая сумма и количество товаров -->
          <div class="border-t-2 border-gray-200 pt-6 bg-gray-50 p-5 rounded-lg">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div class="text-sm text-gray-600 space-y-1">
                <p>Всего товаров: <span class="font-medium text-gray-900">{{ totalItems }}</span> шт.</p>
                <p v-if="orderDetails.delivery?.method_name">
                  Доставка: <span class="font-medium text-gray-900">{{ orderDetails.delivery?.method_name }}</span>
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-600 mb-1">Общая сумма заказа:</p>
                <p class="text-4xl font-bold text-red-600">{{ formatPrice(orderDetails.order.total_amount) }}</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивных хуков Vue и библиотеки для HTTP-запросов
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// === STATE: Реактивные переменные состояния компонента ===

// Массив всех заказов (загружается с сервера)
const orders = ref([]);

// Флаг загрузки списка заказов
const loading = ref(true);

// Текст ошибки при неудачной загрузке заказов
const error = ref('');

// Значение фильтра по статусу (пустая строка = все статусы)
const statusFilter = ref('');

// ID выбранного заказа для отображения в модальном окне
const selectedOrder = ref(null);

// Объект с детальной информацией о выбранном заказе
const orderDetails = ref(null);

// Флаг загрузки деталей заказа
const loadingDetails = ref(false);

// === COMPUTED: Вычисляемые свойства ===

// Фильтрация заказов по выбранному статусу
const filteredOrders = computed(() => {
  if (!statusFilter.value) return orders.value;
  return orders.value.filter(o => o.status === statusFilter.value);
});

// Подсчёт общего количества товаров в выбранном заказе
const totalItems = computed(() => {
  if (!orderDetails.value?.items) return 0;
  return orderDetails.value.items.reduce((sum, item) => sum + item.quantity, 0);
});

// === УТИЛИТЫ: Вспомогательные функции форматирования ===

// Форматирует числовое значение в строку с валютой (рубли) и разделителями тысяч
// Обрабатывает ошибки преобразования и невалидные значения
const formatPrice = (value) => {
  try {
    if (value === undefined || value === null || value === '') return '0 ₽';
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (isNaN(num)) return '0 ₽';
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(num);
  } catch (e) {
    console.error('Ошибка форматирования цены:', e);
    return '0 ₽';
  }
};

// Форматирует дату из ISO-строки в локальный формат с датой и временем
// Обрабатывает ошибки парсинга даты
const formatDate = (dateString) => {
  if (!dateString) return '—';
  try {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch (e) {
    return '—';
  }
};

// Возвращает классы Tailwind для цветного бейджа статуса заказа
const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

// Преобразует технический ключ статуса в читаемое название на русском
const getStatusText = (status) => {
  const texts = {
    pending: 'Новый',
    confirmed: 'Подтверждён',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён'
  };
  return texts[status] || status;
};

// Преобразует ключ способа оплаты в читаемое описание
const getPaymentMethod = (method) => {
  const methods = {
    card: 'Банковская карта',
    cash: 'Наличными при получении',
    sbp: 'СБП (Система быстрых платежей)'
  };
  return methods[method] || method;
};

// === API-МЕТОДЫ: Асинхронные запросы к серверу ===

// Загрузка списка заказов с сервера
const loadOrders = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const token = localStorage.getItem('api_token');
    
    const res = await axios.get('http://127.0.0.1:8000/api/admin/orders', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    // Нормализация ответа: поддержка разных форматов данных от сервера
    orders.value = res.data.data || res.data || [];
    
  } catch (err) {
    console.error('Ошибка загрузки заказов:', err);
    // Обработка ошибок по кодам статуса HTTP
    if (err.response?.status === 401 || err.response?.status === 403) {
      error.value = 'Нет доступа. Перезайдите в систему.';
      localStorage.removeItem('api_token');
    } else if (err.response?.status === 500) {
      error.value = 'Ошибка сервера: ' + (err.response.data?.message || '');
    } else {
      error.value = 'Не удалось загрузить заказы';
    }
  } finally {
    // Сброс флага загрузки независимо от результата
    loading.value = false;
  }
};

// Загрузка детальной информации о заказе по ID
const loadOrderDetails = async (orderId) => {
  selectedOrder.value = orderId;
  loadingDetails.value = true;
  orderDetails.value = null;
  
  try {
    const token = localStorage.getItem('api_token');
    
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/orders/${orderId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    orderDetails.value = res.data;
    
  } catch (err) {
    console.error('Ошибка загрузки деталей заказа:', err);
    alert('Ошибка загрузки деталей заказа');
  } finally {
    loadingDetails.value = false;
  }
};

// Обновление статуса заказа через API
const updateStatus = async (orderId, status) => {
  try {
    const token = localStorage.getItem('api_token');
    await axios.put(`http://127.0.0.1:8000/api/admin/orders/${orderId}/status`, 
      { status },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        }
      }
    );
    // Перезагружаем список заказов для отображения обновлённого статуса
    await loadOrders();
  } catch (err) {
    console.error('Ошибка обновления статуса:', err);
    alert('Ошибка обновления статуса: ' + (err.response?.data?.message || ''));
  }
};

// Удаление заказа с подтверждением пользователя
const deleteOrder = async (orderId) => {
  if (!confirm('Удалить заказ?')) return;
  
  try {
    const token = localStorage.getItem('api_token');
    await axios.delete(`http://127.0.0.1:8000/api/admin/orders/${orderId}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    // Перезагружаем список после успешного удаления
    await loadOrders();
  } catch (err) {
    console.error('Ошибка удаления заказа:', err);
    alert('Ошибка удаления: ' + (err.response?.data?.message || ''));
  }
};

// Скачивание отчёта по заказам в формате Excel
const downloadOrdersExcel = async () => {
  try {
    const token = localStorage.getItem('api_token');
    
    // Запрос файла с сервера с указанием responseType: 'blob' для бинарных данных
    const response = await axios.get('http://127.0.0.1:8000/api/admin/reports/orders/excel', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      },
      responseType: 'blob'
    });
    
    // Создание Blob-объекта из полученных данных
    const blob = new Blob([response.data], { 
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
    });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    
    // Формирование имени файла с текущей датой
    const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    link.href = url;
    link.setAttribute('download', `orders_report_${date}.xlsx`);
    
    // Программный клик по ссылке для запуска скачивания
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
    
  } catch (err) {
    console.error('Ошибка скачивания Excel-отчёта:', err);
    
    let message = 'Не удалось скачать Excel-отчёт';
    // Обработка ошибок авторизации и серверных ошибок
    if (err.response?.status === 401 || err.response?.status === 403) {
      message = 'Нет доступа. Перезайдите в систему.';
      localStorage.removeItem('api_token');
    } else if (err.response?.data?.message) {
      message = err.response.data.message;
    }
    
    alert(message);
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ ===

// Загрузка списка заказов при монтировании компонента
onMounted(() => {
  loadOrders();
});
</script>