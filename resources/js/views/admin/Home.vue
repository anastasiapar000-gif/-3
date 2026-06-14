<template>
  <!-- Корневой контейнер страницы дашборда с отступами между секциями -->
  <div class="space-y-8">
    
    <!-- Секция статистики: 4 карточки в сетке (адаптивная: 1 колонка на мобильных, 4 на десктопе) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      
      <!-- Карточка: Всего товаров -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <!-- Подпись метрики -->
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Всего товаров</p>
            <!-- Значение метрики (подставляется из реактивной переменной stats) -->
            <p class="text-3xl font-bold text-gray-900">{{ stats.products }}</p>
          </div>
          <!-- Иконка карточки: товары (SVG) -->
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Карточка: Заказов -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Заказов</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.orders }}</p>
          </div>
          <!-- Иконка карточки: заказы (SVG) -->
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Карточка: Пользователей -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Пользователей</p>
            <p class="text-3xl font-bold text-gray-900">{{ stats.users }}</p>
          </div>
          <!-- Иконка карточки: пользователи (SVG) -->
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Карточка: Выручка -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Выручка</p>
            <!-- Форматирование цены через вспомогательную функцию -->
            <p class="text-3xl font-bold text-gray-900">{{ formatPrice(stats.revenue) }}</p>
          </div>
          <!-- Иконка карточки: деньги (SVG) -->
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Секция: Последние заказы (таблица) -->
    <div class="bg-white border border-gray-200 rounded-lg">
      <!-- Заголовок секции -->
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 tracking-wide">Последние заказы</h2>
      </div>
      
      <!-- Контейнер с горизонтальной прокруткой для адаптивности таблицы -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <!-- Заголовки таблицы -->
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">№ Заказа</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Клиент</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Сумма</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Статус</th>
              <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Дата</th>
            </tr>
          </thead>
          
          <!-- Тело таблицы: список заказов -->
          <tbody class="divide-y divide-gray-200">
            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50">
              <!-- Номер заказа -->
              <td class="px-6 py-4 text-sm text-gray-900">#{{ order.id }}</td>
              
              <!-- Имя клиента -->
              <td class="px-6 py-4 text-sm text-gray-700">{{ order.customer_name }}</td>
              
              <!-- Сумма заказа с форматированием -->
              <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ formatPrice(order.total) }}</td>
              
              <!-- Статус заказа: цветной бейдж в зависимости от значения -->
              <td class="px-6 py-4">
                <span 
                  class="px-3 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-yellow-100 text-yellow-800': order.status === 'pending',
                    'bg-blue-100 text-blue-800': order.status === 'confirmed' || order.status === 'processing',
                    'bg-purple-100 text-purple-800': order.status === 'shipped',
                    'bg-green-100 text-green-800': order.status === 'delivered' || order.status === 'completed',
                    'bg-red-100 text-red-800': order.status === 'cancelled'
                  }"
                >
                  {{ getStatusText(order.status) }}
                </span>
              </td>
              
              <!-- Дата создания заказа с форматированием -->
              <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(order.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
// Импорт реактивных хуков Vue и библиотеки для HTTP-запросов
import { ref, onMounted } from 'vue';
import axios from 'axios';

// === STATE: Реактивные переменные для хранения данных дашборда ===

// Объект статистики: инициализируется нулевыми значениями
const stats = ref({
  products: 0,  // Количество товаров
  orders: 0,    // Количество заказов
  users: 0,     // Количество пользователей
  revenue: 0    // Общая выручка (только по доставленным заказам)
});

// Массив последних заказов для отображения в таблице
const recentOrders = ref([]);

// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Форматирует числовое значение в строку с разделителями тысяч и символом рубля
// Пример: 12500.5 -> "12 500,5 ₽"
const formatPrice = (value) => {
  const num = Number(value);
  // Если значение не является числом — возвращаем "0 ₽"
  return isNaN(num) ? '0 ₽' : `${num.toLocaleString('ru-RU')} ₽`;
};

// Преобразует технический ключ статуса в читаемое название на русском
const getStatusText = (status) => {
  const statuses = {
    pending: 'Ожидает',
    confirmed: 'Подтверждён',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    completed: 'Выполнен',
    cancelled: 'Отменён'
  };
  // Если статус не найден в словаре — возвращаем исходное значение
  return statuses[status] || status;
};

// Форматирует дату из ISO-строки в локальный формат "ДД.ММ.ГГГГ"
const formatDate = (date) => {
  return new Date(date).toLocaleDateString('ru-RU');
};

// === API-МЕТОДЫ ===

// Загрузка данных для дашборда: статистика + последние заказы
const loadDashboardData = async () => {
  try {
    // Получаем токен авторизации из хранилища
    const token = localStorage.getItem('api_token');
    
    // Параллельная загрузка двух эндпоинтов через Promise.all для ускорения
    const [statsRes, ordersRes] = await Promise.all([
      // Запрос статистики
      axios.get('http://127.0.0.1:8000/api/admin/stats', {
        headers: { 'Authorization': `Bearer ${token}` }
      }),
      // Запрос последних заказов
      axios.get('http://127.0.0.1:8000/api/admin/orders/recent', {
        headers: { 'Authorization': `Bearer ${token}` }
      })
    ]);

    // Обновление реактивных переменных полученными данными
    stats.value = statsRes.data;
    recentOrders.value = ordersRes.data;
    
  } catch (error) {
    // Логирование ошибки в консоль разработчика
    console.error('Ошибка загрузки дашборда:', error);
    // В продакшене здесь можно добавить отображение уведомления пользователю
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ ===

// Загрузка данных при монтировании компонента (после первого рендера)
onMounted(() => {
  loadDashboardData();
});
</script>