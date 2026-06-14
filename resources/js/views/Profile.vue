<template>
  <!-- Корневой контейнер страницы личного кабинета: минимальная высота, фон, отступы -->
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
      
      <!-- Заголовок страницы: название и описание раздела -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Личный кабинет</h1>
        <p class="mt-1 text-sm text-gray-600">Управление профилем и заказами</p>
      </div>
      
      <!-- Сетка макета: боковое меню (3 колонки) + основной контент (9 колонок) на десктопе -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Боковое меню навигации: закреплено в левой колонке -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <!-- Блок профиля пользователя: аватар, имя, email -->
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center gap-4">
                <!-- Аватар: первая буква имени на градиентном фоне -->
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white font-bold text-xl shadow-md">
                  {{ userInitial }}
                </div>
                <!-- Информация о пользователе: имя и email с обрезкой длинного текста -->
                <div class="flex-1 min-w-0">
                  <h2 class="font-semibold text-gray-900 truncate">{{ displayName }}</h2>
                  <p class="text-sm text-gray-500 truncate">{{ authStore.user?.email }}</p>
                </div>
              </div>
            </div>
            
            <!-- Навигационное меню: кнопки переключения вкладок -->
            <nav class="p-2">
              
              <!-- Кнопка вкладки "Профиль": активный стиль при совпадении activeTab -->
              <button 
                @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-red-50 text-red-700 border-r-4 border-red-600' : 'text-gray-700 hover:bg-gray-50'"
                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-3"
              >
                <!-- Иконка профиля (SVG) -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Профиль
              </button>
              
              <!-- Кнопка вкладки "Заказы": с бейджем количества заказов -->
              <button 
                @click="activeTab = 'orders'"
                :class="activeTab === 'orders' ? 'bg-red-50 text-red-700 border-r-4 border-red-600' : 'text-gray-700 hover:bg-gray-50'"
                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-3"
              >
                <!-- Иконка заказов (корзина, SVG) -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Мои заказы
                <!-- Бейдж с количеством заказов: показывается если есть заказы -->
                <span v-if="orders.length > 0" class="ml-auto bg-gray-100 text-gray-600 text-xs font-semibold px-2 py-1 rounded-full">
                  {{ orders.length }}
                </span>
              </button>
              
              <!-- Кнопка вкладки "Адреса доставки" -->
              <button 
                @click="activeTab = 'addresses'"
                :class="activeTab === 'addresses' ? 'bg-red-50 text-red-700 border-r-4 border-red-600' : 'text-gray-700 hover:bg-gray-50'"
                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-3"
              >
                <!-- Иконка адреса (маркер, SVG) -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Адреса доставки
              </button>
              
              <!-- Разделитель меню -->
              <div class="border-t border-gray-200 my-2"></div>
              
              <!-- Кнопка выхода из аккаунта -->
              <button 
                @click="logout"
                class="w-full text-left px-4 py-3 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-all duration-200 flex items-center gap-3"
              >
                <!-- Иконка выхода (стрелка, SVG) -->
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Выйти
              </button>
            </nav>
          </div>
        </div>

        <!-- Основной контент: динамически меняется в зависимости от activeTab -->
        <div class="lg:col-span-9">
          
          <!-- === ВКЛАДКА: ПРОФИЛЬ ПОЛЬЗОВАТЕЛЯ === -->
          <div v-if="activeTab === 'profile'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Редактирование профиля</h2>
              <p class="mt-1 text-sm text-gray-600">Измените данные вашего аккаунта</p>
            </div>
            
            <!-- Форма редактирования профиля: отправка предотвращает перезагрузку -->
            <form @submit.prevent="updateProfile" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Поле: ФИО -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">ФИО</label>
                  <input 
                    v-model="profileForm.full_name" 
                    type="text" 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    placeholder="Иванов Иван Иванович"
                  >
                </div>
                
                <!-- Поле: Email (обязательное) -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-gray-400 font-normal ml-1">(обязательно)</span>
                  </label>
                  <input 
                    v-model="profileForm.email" 
                    type="email" 
                    required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    placeholder="your@email.com"
                  >
                </div>
                
                <!-- Поле: Телефон -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Телефон</label>
                  <input 
                    v-model="profileForm.phone" 
                    type="tel" 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                    placeholder="+7 (999) 123-45-67"
                  >
                </div>
              </div>
              
              <!-- Кнопка сохранения и индикатор успеха -->
              <div class="pt-4 flex items-center gap-4">
                <button 
                  type="submit" 
                  :disabled="loading"
                  class="px-8 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ loading ? 'Сохранение...' : 'Сохранить изменения' }}
                </button>
                <!-- Сообщение об успешном сохранении: показывается на 3 секунды -->
                <span v-if="saveSuccess" class="text-sm text-green-600 font-medium">
                  Изменения сохранены
                </span>
              </div>
            </form>
          </div>

          <!-- === ВКЛАДКА: ИСТОРИЯ ЗАКАЗОВ === -->
          <div v-if="activeTab === 'orders'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="mb-6">
              <h2 class="text-xl font-semibold text-gray-900">История заказов</h2>
              <p class="mt-1 text-sm text-gray-600">Нажмите на заказ, чтобы увидеть детали</p>
            </div>
            
            <!-- Индикатор загрузки заказов -->
            <div v-if="loadingOrders" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-red-600 border-t-transparent"></div>
              <p class="mt-3 text-gray-600">Загрузка заказов...</p>
            </div>
            
            <!-- Состояние: нет заказов -->
            <div v-else-if="orders.length === 0" class="text-center py-16">
              <!-- Иконка пустой корзины (SVG) -->
              <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              <p class="text-gray-600 mb-4">У вас пока нет заказов</p>
              <!-- Кнопка перехода в каталог -->
              <router-link to="/catalog" class="inline-flex items-center px-6 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">
                Перейти в каталог
              </router-link>
            </div>
            
            <!-- Список заказов: карточки с основной информацией -->
            <div v-else class="space-y-4">
              <div 
                v-for="order in orders" 
                :key="order.id" 
                class="border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow cursor-pointer"
                @click="openOrderDetails(order.id)"
              >
                <!-- Заголовок карточки: номер заказа, дата, статус -->
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 mb-4">
                  <div>
                    <p class="font-semibold text-lg text-gray-900">Заказ №{{ order.order_number || order.id }}</p>
                    <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                  </div>
                  <!-- Бейдж статуса с динамическим цветом -->
                  <span 
                    :class="getStatusClass(order.status)"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wide"
                  >
                    {{ getStatusText(order.status) }}
                  </span>
                </div>
                
                <!-- Список товаров в заказе (показываем первые 3) -->
                <div class="space-y-2 mb-4">
                  <div 
                    v-for="item in order.items?.slice(0, 3)" 
                    :key="item.id"
                    class="flex justify-between text-sm py-2 border-b border-gray-100 last:border-0"
                  >
                    <span class="text-gray-600">{{ item.product_name || item.name }} <span class="text-gray-400">× {{ item.quantity }}</span></span>
                    <span class="font-medium text-gray-900">{{ formatPrice(item.price * item.quantity) }}</span>
                  </div>
                  <!-- Индикатор дополнительных товаров если их больше 3 -->
                  <div v-if="order.items?.length > 3" class="text-xs text-gray-500">
                    + ещё {{ order.items.length - 3 }} товар{{ order.items.length - 3 === 1 ? '' : 'а' }}
                  </div>
                </div>
                
                <!-- Итоговая сумма заказа -->
                <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                  <span class="font-semibold text-gray-700">Итого:</span>
                  <span class="text-2xl font-bold text-red-600">{{ formatPrice(order.total_amount || order.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- === МОДАЛЬНОЕ ОКНО: ДЕТАЛИ ЗАКАЗА === -->
          <div v-if="selectedOrder" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="closeOrderDetails">
            <!-- Контент модального окна: клик внутри не закрывает окно -->
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto animate-fade-in" @click.stop>
              
              <!-- Заголовок модального окна: номер заказа и кнопка закрытия -->
              <div class="flex justify-between items-start p-6 border-b border-gray-200">
                <div>
                  <h3 class="text-xl font-bold text-gray-900">Заказ №{{ selectedOrder.order_number || selectedOrder.id }}</h3>
                  <p class="text-sm text-gray-500 mt-1">{{ formatDate(selectedOrder.created_at) }}</p>
                </div>
                <!-- Кнопка закрытия (крестик) -->
                <button @click="closeOrderDetails" class="text-gray-400 hover:text-gray-600 transition p-1">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>

              <!-- Детали заказа: сетка с информацией -->
              <div class="p-6 space-y-6">
                
                <!-- Блок: Статус и способ оплаты -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-1">Статус</p>
                    <span :class="getStatusClass(selectedOrder.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wide">
                      {{ getStatusText(selectedOrder.status) }}
                    </span>
                  </div>
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-xs text-gray-500 uppercase font-medium mb-1">Оплата</p>
                    <p class="font-medium text-gray-900">{{ getPaymentText(selectedOrder.payment_method) }}</p>
                  </div>
                </div>

                <!-- Блок: Информация о доставке -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-xs text-gray-500 uppercase font-medium mb-1">Доставка</p>
                  <p class="font-medium text-gray-900">{{ selectedOrder.delivery_method_name || selectedOrder.delivery_method }}</p>
                  <p class="text-sm text-gray-600 mt-1">Стоимость: {{ selectedOrder.delivery_price_formatted || formatPrice(selectedOrder.delivery_price) }}</p>
                </div>

                <!-- Блок: Адрес получения -->
                <div class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-xs text-gray-500 uppercase font-medium mb-1">Адрес получения</p>
                  <p class="font-medium text-gray-900">{{ selectedOrder.shipping_address_full || '—' }}</p>
                </div>

                <!-- Блок: Комментарий к заказу (если есть) -->
                <div v-if="selectedOrder.comment" class="bg-gray-50 p-4 rounded-lg">
                  <p class="text-xs text-gray-500 uppercase font-medium mb-1">Комментарий к заказу</p>
                  <p class="text-gray-800 whitespace-pre-wrap">{{ selectedOrder.comment }}</p>
                </div>

                <!-- Блок: Состав заказа (список товаров) -->
                <div class="border-t border-gray-200 pt-4">
                  <h4 class="font-semibold text-gray-900 mb-3">Состав заказа</h4>
                  <div class="space-y-3">
                    <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center py-2 border-b border-gray-100 last:border-0">
                      <div class="flex-1 pr-4">
                        <p class="font-medium text-gray-900">{{ item.product_name || item.name }}</p>
                        <p v-if="item.size" class="text-xs text-gray-500">Размер: {{ item.size }}</p>
                        <p class="text-xs text-gray-500">× {{ item.quantity }} шт.</p>
                      </div>
                      <p class="font-semibold text-gray-900 whitespace-nowrap">{{ formatPrice(item.price * item.quantity) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Итоговая сумма к оплате -->
                <div class="border-t-2 border-gray-200 pt-4 flex justify-between items-center">
                  <span class="text-lg font-semibold text-gray-700">Итого к оплате:</span>
                  <span class="text-2xl font-bold text-red-600">{{ formatPrice(selectedOrder.total_amount || selectedOrder.total) }}</span>
                </div>
              </div>

              <!-- Футер модального окна: кнопка закрытия -->
              <div class="p-6 border-t border-gray-200 flex justify-end bg-gray-50 rounded-b-xl">
                <button @click="closeOrderDetails" class="px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                  Закрыть
                </button>
              </div>
            </div>
          </div>

          <!-- === ВКЛАДКА: АДРЕСА ДОСТАВКИ === -->
          <div v-if="activeTab === 'addresses'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 sm:p-8">
            <!-- Заголовок и кнопка добавления адреса -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Адреса доставки</h2>
                <p class="mt-1 text-sm text-gray-600">Управление адресами доставки</p>
              </div>
              <button 
                @click="showAddAddress = true"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 transition-all"
              >
                <!-- Иконка добавления (плюс, SVG) -->
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Добавить адрес
              </button>
            </div>
            
            <!-- Индикатор загрузки адресов -->
            <div v-if="loadingAddresses" class="text-center py-12">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-red-600 border-t-transparent"></div>
              <p class="mt-3 text-gray-600">Загрузка адресов...</p>
            </div>
            
            <!-- Состояние: нет сохранённых адресов -->
            <div v-else-if="!addresses || addresses.length === 0" class="text-center py-12">
              <!-- Иконка адреса (маркер, SVG) -->
              <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <p class="text-gray-600">У вас нет сохранённых адресов</p>
            </div>
            
            <!-- Список адресов: сетка карточек -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="addr in addresses" 
                :key="getAddressId(addr)"
                :class="addr.is_default ? 'border-red-300 bg-red-50' : 'border-gray-200'"
                class="border rounded-lg p-5 hover:shadow-md transition-shadow"
              >
                <!-- Заголовок карточки: город, улица, метка "Основной" -->
                <div class="flex justify-between items-start mb-3">
                  <h3 class="font-semibold text-gray-900">
                    {{ addr.city || '—' }}, {{ addr.street || '—' }}
                  </h3>
                  <span v-if="addr.is_default" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Основной
                  </span>
                </div>
                <!-- Детали адреса: дом, квартира, телефон -->
                <p class="text-sm text-gray-600 mb-2">
                  {{ addr.building || '—' }}{{ addr.apartment ? ', кв. ' + addr.apartment : '' }}
                </p>
                <p class="text-sm text-gray-600 mb-4">{{ addr.phone || '—' }}</p>
                
                <!-- Кнопки действий: сделать основным, удалить -->
                <div class="flex gap-2">
                  <button 
                    v-if="!addr.is_default"
                    @click="setDefaultAddress(getAddressId(addr))"
                    class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    Сделать основным
                  </button>
                  <button 
                    @click="deleteAddress(getAddressId(addr))"
                    class="text-sm text-red-600 hover:text-red-700 font-medium"
                  >
                    Удалить
                  </button>
                </div>
              </div>
            </div>
          </div>
          
        </div>
      </div>
    </div>

    <!-- === МОДАЛЬНОЕ ОКНО: ДОБАВИТЬ АДРЕС === -->
    <div v-if="showAddAddress" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <!-- Контент модального окна -->
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 animate-fade-in">
        
        <!-- Заголовок и кнопка закрытия -->
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Добавить адрес</h3>
          <button @click="showAddAddress = false" class="text-gray-400 hover:text-gray-600 transition">
            <!-- Иконка закрытия (крестик, SVG) -->
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Форма добавления адреса -->
        <form @submit.prevent="addAddress" class="space-y-4">
          
          <!-- Поле: Город (обязательное) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Город *</label>
            <input v-model="newAddress.city" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Москва">
          </div>
          
          <!-- Поле: Улица (обязательное) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Улица *</label>
            <input v-model="newAddress.street" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="ул. Примерная">
          </div>
          
          <!-- Поля: Дом и Квартира (сетка 2 колонки) -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Дом *</label>
              <input v-model="newAddress.building" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="1">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Квартира</label>
              <input v-model="newAddress.apartment" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="10">
            </div>
          </div>
          
          <!-- Поле: Телефон (обязательное) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Телефон *</label>
            <input v-model="newAddress.phone" required type="tel" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="+7 (999) 123-45-67">
          </div>
          
          <!-- Кнопки действий: Отмена и Сохранить -->
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showAddAddress = false" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">Отмена</button>
            <button type="submit" class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition">Сохранить</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивных хуков и хуков жизненного цикла Vue 3
import { ref, computed, onMounted, watch } from 'vue';

// Импорт библиотеки для отправки HTTP-запросов
import axios from 'axios';

// Импорт хранилища аутентификации (Pinia) для доступа к данным пользователя
import { useAuthStore } from '../stores/auth';

// Импорт роутера для программной навигации
import { useRouter } from 'vue-router';

// === ИНИЦИАЛИЗАЦИЯ ===

// Получение экземпляров хранилища и роутера
const authStore = useAuthStore();
const router = useRouter();

// Базовый URL API для пользовательских эндпоинтов
const API_URL = 'http://127.0.0.1:8000/api';

// === STATE: Реактивные переменные состояния ===

// Активная вкладка личного кабинета: 'profile' | 'orders' | 'addresses'
const activeTab = ref('profile');

// Флаги состояния загрузки для разных секций
const loading = ref(false);           // Загрузка обновления профиля
const loadingOrders = ref(false);     // Загрузка списка заказов
const loadingAddresses = ref(false);  // Загрузка списка адресов

// Флаг успешного сохранения профиля (для показа сообщения)
const saveSuccess = ref(false);

// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Формирование заголовков для авторизованных запросов к API
const getAuthHeaders = () => {
  const token = localStorage.getItem('api_token');
  return {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  };
};

// === STATE: Формы и данные ===

// Объект формы редактирования профиля (изначально пустой, заполняется через watch)
const profileForm = ref({
  full_name: '',
  email: '',
  phone: ''
});

// Синхронизация формы профиля с данными пользователя из хранилища
// Срабатывает при изменении authStore.user и при первом монтировании (immediate: true)
watch(() => authStore.user, (newUser) => {
  if (newUser) {
    profileForm.value = {
      full_name: newUser.full_name || '',
      email: newUser.email || '',
      phone: newUser.phone || ''
    };
  }
}, { immediate: true, deep: true });

// Массивы данных: заказы и адреса доставки
const orders = ref([]);
const addresses = ref([]);

// Состояние модального окна добавления адреса
const showAddAddress = ref(false);

// Объект формы для нового адреса доставки
const newAddress = ref({
  city: '',
  street: '',
  building: '',
  apartment: '',
  phone: ''
});

// Объект для хранения деталей выбранного заказа (для модального окна)
const selectedOrder = ref(null);

// === COMPUTED: Вычисляемые свойства ===

// Первая буква имени пользователя для аватара-заглушки
const userInitial = computed(() => {
  const name = authStore.user?.full_name || authStore.user?.email?.split('@')[0] || 'U';
  return name.charAt(0).toUpperCase();
});

// Отображаемое имя пользователя: приоритет full_name → часть email → заглушка
const displayName = computed(() => {
  return authStore.user?.full_name || authStore.user?.email?.split('@')[0] || 'Пользователь';
});

// Форматирование цены в рублях с разделителями тысяч и обработкой ошибок
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
    return '0 ₽';
  }
};

// Получение уникального идентификатора адреса: поддержка разных форматов от сервера
const getAddressId = (addr) => {
  return addr?.address_id || addr?.id || addr?.addressId || null;
};

// === МЕТОДЫ: Работа с заказами ===

// Открытие модального окна с деталями заказа: загрузка данных по ID
const openOrderDetails = async (orderId) => {
  try {
    const res = await axios.get(`${API_URL}/user/orders/${orderId}`, {
      headers: getAuthHeaders()
    });
    selectedOrder.value = res.data;
  } catch (error) {
    console.error('Ошибка загрузки деталей заказа:', error);
    alert('Не удалось загрузить детали заказа');
  }
};

// Закрытие модального окна деталей заказа
const closeOrderDetails = () => {
  selectedOrder.value = null;
};

// Преобразование ключа способа оплаты в читаемое название
const getPaymentText = (method) => {
  const map = { 'card': 'Банковская карта', 'cash': 'Наличными', 'sbp': 'СБП' };
  return map[method] || method;
};

// Получение классов Tailwind для цветного бейджа статуса заказа
const getStatusClass = (status) => {
  const map = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'processing': 'bg-purple-100 text-purple-800',
    'shipped': 'bg-indigo-100 text-indigo-800',
    'delivered': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800'
  };
  return map[status] || 'bg-gray-100 text-gray-800';
};

// Преобразование технического ключа статуса в читаемое название на русском
const getStatusText = (status) => {
  const map = {
    'pending': 'Новый',
    'confirmed': 'Подтверждён',
    'processing': 'В обработке',
    'shipped': 'Отправлен',
    'delivered': 'Доставлен',
    'cancelled': 'Отменён'
  };
  return map[status] || status;
};

// Форматирование даты из ISO-строки в локальный формат с датой и временем
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

// === API-МЕТОДЫ: Профиль пользователя ===

// Загрузка актуальных данных профиля пользователя с сервера
const loadUserProfile = async () => {
  try {
    const token = localStorage.getItem('api_token');
    if (!token) return;
    
    const res = await axios.get(`${API_URL}/user`, {
      headers: getAuthHeaders()
    });
    
    // Извлечение данных пользователя: поддержка разных форматов ответа
    const userData = res.data.user || res.data;
    
    // Обновление хранилища Pinia и localStorage для синхронизации состояния
    authStore.user = userData;
    localStorage.setItem('user', JSON.stringify(userData));
    
  } catch (error) {
    console.error('Ошибка загрузки профиля:', error);
    // Обработка истечения сессии: очистка токена и перенаправление на вход
    if (error.response?.status === 401) {
      localStorage.removeItem('api_token');
      router.push('/login');
    }
  }
};

// Обновление данных профиля пользователя через API
const updateProfile = async () => {
  loading.value = true;
  saveSuccess.value = false;
  try {
    const res = await axios.put(`${API_URL}/user/profile`, profileForm.value, {
      headers: getAuthHeaders()
    });
    
    // Извлечение обновлённых данных пользователя из ответа
    const userData = res.data.user || res.data;
    
    // Обновление хранилища и localStorage
    authStore.user = userData;
    localStorage.setItem('user', JSON.stringify(userData));
    
    // Показ сообщения об успехе на 3 секунды
    saveSuccess.value = true;
    setTimeout(() => saveSuccess.value = false, 3000);
    
  } catch (error) {
    // Обработка ошибок авторизации и серверных ошибок
    if (error.response?.status === 401) {
      alert('Сессия истекла. Пожалуйста, войдите снова.');
      localStorage.removeItem('api_token');
      router.push('/login');
    } else {
      alert('Ошибка: ' + (error.response?.data?.message || error.message));
    }
  } finally {
    loading.value = false;
  }
};

// === API-МЕТОДЫ: Заказы ===

// Загрузка списка заказов пользователя с сервера
const loadOrders = async () => {
  loadingOrders.value = true;
  try {
    const res = await axios.get(`${API_URL}/user/orders`, {
      headers: getAuthHeaders()
    });
    // Нормализация ответа: поддержка разных форматов данных
    orders.value = res.data?.data || res.data || [];
  } catch (error) {
    // Обработка истечения сессии
    if (error.response?.status === 401) {
      localStorage.removeItem('api_token');
      router.push('/login');
    }
  } finally {
    loadingOrders.value = false;
  }
};

// === API-МЕТОДЫ: Адреса доставки ===

// Загрузка списка адресов доставки пользователя
const loadAddresses = async () => {
  loadingAddresses.value = true;
  try {
    const res = await axios.get(`${API_URL}/user/addresses`, {
      headers: getAuthHeaders()
    });
    addresses.value = res.data?.data || res.data || [];
  } catch (error) {
    if (error.response?.status === 401) {
      localStorage.removeItem('api_token');
      router.push('/login');
    }
  } finally {
    loadingAddresses.value = false;
  }
};

// Добавление нового адреса доставки через API
const addAddress = async () => {
  try {
    const res = await axios.post(`${API_URL}/user/addresses`, newAddress.value, {
      headers: getAuthHeaders()
    });
    // Извлечение нового адреса из ответа и добавление в локальный массив
    const newAddr = res.data?.address || res.data?.data || res.data;
    if (newAddr) addresses.value.push(newAddr);
    
    // Закрытие модального окна и сброс формы
    showAddAddress.value = false;
    newAddress.value = { city: '', street: '', building: '', apartment: '', phone: '' };
  } catch (error) {
    alert('Ошибка добавления адреса: ' + (error.response?.data?.message || ''));
  }
};

// Установка адреса как основного (по умолчанию) через API
const setDefaultAddress = async (id) => {
  try {
    await axios.put(`${API_URL}/user/addresses/${id}/default`, {}, {
      headers: getAuthHeaders()
    });
    // Перезагрузка списка адресов для отображения обновлённого статуса
    await loadAddresses();
  } catch (error) {
    alert('Ошибка: ' + (error.response?.data?.message || ''));
  }
};

// Удаление адреса доставки: запрос подтверждения и отправка DELETE-запроса
const deleteAddress = async (id) => {
  if (!id || !confirm('Удалить этот адрес?')) return;
  try {
    await axios.delete(`${API_URL}/user/addresses/${id}`, {
      headers: getAuthHeaders()
    });
    // Удаление адреса из локального массива без перезагрузки всего списка
    addresses.value = addresses.value.filter(a => getAddressId(a) !== id);
  } catch (error) {
    alert('Ошибка удаления: ' + (error.response?.data?.message || ''));
  }
};

// === МЕТОДЫ: Выход из системы ===

// Обработчик выхода: подтверждение, вызов метода из хранилища, перенаправление
const logout = async () => {
  if (confirm('Выйти из аккаунта?')) {
    await authStore.logout();
    router.push('/');
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ КОМПОНЕНТА ===

// Инициализация при монтировании: загрузка профиля ПЕРЕД остальными данными
onMounted(async () => {
  await loadUserProfile(); // Загружаем актуальные данные профиля из БД
  loadOrders();            // Параллельно загружаем заказы
  loadAddresses();         // И адреса доставки
});
</script>

<style scoped>
/* Анимация появления модальных окон: плавное увеличение и проявление */
.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}

/* Ключевые кадры анимации fadeIn: от прозрачного масштаба 0.95 к полному */
@keyframes fadeIn {
  from { 
    opacity: 0; 
    transform: scale(0.95);
  }
  to { 
    opacity: 1; 
    transform: scale(1);
  }
}
</style>