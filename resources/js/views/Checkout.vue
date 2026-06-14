<template>
  <div class="min-h-screen bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-12 tracking-tight">
        Оформление заказа
      </h1>

      <!-- Успешный заказ -->
      <div v-if="orderSuccess" class="text-center py-20">
        <div class="w-20 h-20 mx-auto mb-6 bg-green-100 rounded-full flex items-center justify-center">
          <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Заказ оформлен!</h2>
        <p class="text-gray-600 mb-8">Номер заказа: #{{ orderNumber }}</p>
        <p class="text-sm text-gray-500 mb-8">Мы отправили подтверждение на {{ formData.email }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link to="/catalog" class="px-8 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold tracking-wider rounded-lg transition-colors">
            Продолжить покупки
          </router-link>
          <button @click="resetOrder" class="px-8 py-3 border border-gray-300 hover:border-red-600 text-gray-700 hover:text-red-600 text-sm font-semibold tracking-wider rounded-lg transition-colors">
            На главную
          </button>
        </div>
      </div>

      <!-- Форма заказа -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- Левая колонка: Данные покупателя -->
        <div class="lg:col-span-2 space-y-8">
          
          <!-- Контакты -->
          <section>
            <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">Контактные данные</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Имя *</label>
                <input 
                  v-model="formData.name"
                  @blur="validateField('name')"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-3 border rounded-lg transition focus:ring-1 focus:ring-red-600 focus:border-red-600',
                    errors.name ? 'border-red-500 bg-red-50' : 'border-gray-300'
                  ]"
                  placeholder="Ваше имя"
                >
                <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
              </div>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input 
                    v-model="formData.email"
                    @blur="validateField('email')"
                    type="email"
                    required
                    :class="[
                      'w-full px-4 py-3 border rounded-lg transition focus:ring-1 focus:ring-red-600 focus:border-red-600',
                      errors.email ? 'border-red-500 bg-red-50' : 'border-gray-300'
                    ]"
                    placeholder="email@example.com"
                  >
                  <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Телефон *</label>
                  <input 
                    v-model="formData.phone"
                    @input="formatPhone"
                    @blur="validateField('phone')"
                    type="tel"
                    required
                    maxlength="18"
                    :class="[
                      'w-full px-4 py-3 border rounded-lg transition focus:ring-1 focus:ring-red-600 focus:border-red-600',
                      errors.phone ? 'border-red-500 bg-red-50' : 'border-gray-300'
                    ]"
                    placeholder="+7 (___) ___-__-__"
                  >
                  <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Доставка -->
          <section>
            <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">Доставка</h2>
            
            <!-- Список способов доставки -->
            <div class="space-y-3">
              <label 
                v-for="method in deliveryMethods" 
                :key="method.id"
                :class="[
                  'flex items-start gap-4 p-4 border-2 rounded-lg cursor-pointer transition-all',
                  formData.delivery === method.id 
                    ? 'border-red-600 bg-red-50 ring-1 ring-red-600' 
                    : 'border-gray-300 hover:border-gray-400'
                ]"
              >
                <input 
                  type="radio" 
                  :value="method.id" 
                  v-model="formData.delivery"
                  @change="onDeliveryChange"
                  class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-600 mt-0.5"
                >
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <span class="font-semibold text-gray-900">{{ method.name }}</span>
                    <span class="font-bold text-red-600">{{ method.price === 0 ? 'Бесплатно' : '+' + formatPrice(method.price) }}</span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">{{ method.description }}</p>
                </div>
              </label>
            </div>

            <!--  Поля СДЭК показываются ОТДЕЛЬНО только при выборе СДЭК -->
            <div v-if="formData.delivery === 'cdek'" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Город получения *</label>
                  <input 
                    v-model="formData.cdekCity"
                    @blur="validateField('cdekCity')"
                    type="text"
                    placeholder="Например: Москва"
                    :class="[
                      'w-full px-3 py-2 text-sm border rounded focus:ring-1 focus:ring-red-600',
                      errors.cdekCity ? 'border-red-500 bg-white' : 'border-gray-300'
                    ]"
                  >
                  <p v-if="errors.cdekCity" class="mt-1 text-xs text-red-600">{{ errors.cdekCity }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Адрес пункта выдачи *</label>
                  <input 
                    v-model="formData.cdekAddress"
                    @blur="validateField('cdekAddress')"
                    type="text"
                    placeholder="Улица, дом, пункт СДЭК"
                    :class="[
                      'w-full px-3 py-2 text-sm border rounded focus:ring-1 focus:ring-red-600',
                      errors.cdekAddress ? 'border-red-500 bg-white' : 'border-gray-300'
                    ]"
                  >
                  <p v-if="errors.cdekAddress" class="mt-1 text-xs text-red-600">{{ errors.cdekAddress }}</p>
                </div>
              </div>
              <p class="text-xs text-gray-500 mt-2">Адрес пункта выдачи укажем в подтверждении заказа</p>
            </div>

            <p v-if="errors.delivery" class="mt-2 text-sm text-red-600">{{ errors.delivery }}</p>
          </section>

          <!-- Адрес (только для Иркутска и самовывоза) -->
          <section v-if="formData.delivery !== 'cdek'">
            <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">
              {{ formData.delivery === 'pickup' ? 'Самовывоз' : 'Адрес доставки' }}
            </h2>
            
            <!-- Выбор из сохраненных адресов -->
            <div v-if="formData.delivery === 'irkutsk' && userAddresses.length > 0" class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Выберите из сохраненных адресов:</label>
              <div class="space-y-2">
                <div 
                  v-for="addr in userAddresses" 
                  :key="addr.id"
                  @click="selectAddress(addr)"
                  :class="[
                    'p-4 border-2 rounded-lg cursor-pointer transition-all',
                    selectedAddressId === addr.id 
                      ? 'border-red-600 bg-red-50' 
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">
                        {{ addr.street }}, {{ addr.building }}{{ addr.apartment ? ', кв. ' + addr.apartment : '' }}
                      </p>
                      <p v-if="addr.entrance || addr.floor" class="text-sm text-gray-600 mt-1">
                        {{ addr.entrance ? 'подъезд ' + addr.entrance : '' }}
                        {{ addr.entrance && addr.floor ? ', ' : '' }}
                        {{ addr.floor ? 'этаж ' + addr.floor : '' }}
                      </p>
                      <p v-if="addr.intercom" class="text-sm text-gray-600">Домофон: {{ addr.intercom }}</p>
                    </div>
                    <span v-if="addr.is_default" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Основной
                    </span>
                  </div>
                </div>
                
                <!-- КРАСИВАЯ КНОПКА ДЛЯ РУЧНОГО ВВОДА -->
                <button 
                  @click="showManualAddress = !showManualAddress"
                  class="w-full mt-4 px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-semibold text-gray-600 hover:border-red-500 hover:text-red-600 hover:bg-red-50 transition-all duration-200 flex items-center justify-center gap-2 group"
                >
                  <svg 
                    class="w-4 h-4 transition-transform group-hover:rotate-12" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span>
                    {{ showManualAddress ? 'Скрыть форму ввода' : 'Ввести адрес вручную' }}
                  </span>
                </button>
              </div>
            </div>
            
            <!-- Поля адреса для Иркутска с DaData -->
            <div v-if="formData.delivery === 'irkutsk'" :class="userAddresses.length > 0 && !showManualAddress ? 'opacity-50 pointer-events-none' : ''">
              <div class="space-y-4 relative">
                
                <!-- ПОЛЕ АДРЕСА С DADATA -->
                <div class="relative">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Улица, дом, квартира *</label>
                  <input 
                    ref="addressInputRef"
                    v-model="formData.address"
                    @input="handleAddressInput"
                    @blur="hideSuggestions"
                    @keydown.down.prevent="navigateSuggestions(1)"
                    @keydown.up.prevent="navigateSuggestions(-1)"
                    @keydown.enter.prevent="selectSuggestion(activeSuggestionIndex)"
                    type="text"
                    required
                    :class="[
                      'w-full px-4 py-3 border rounded-lg transition focus:ring-1 focus:ring-red-600 focus:border-red-600',
                      errors.address ? 'border-red-500 bg-red-50' : 'border-gray-300'
                    ]"
                    placeholder="Начните вводить: Ленина 5..."
                    autocomplete="off"
                  >
                  
                  <!--ВЫПАДАЮЩИЙ СПИСОК ПОДСКАЗОК DADATA -->
                  <ul 
                    v-if="showSuggestions && addressSuggestions.length > 0" 
                    class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-60 overflow-y-auto mt-1"
                  >
                    <li 
                      v-for="(suggestion, index) in addressSuggestions" 
                      :key="suggestion.value"
                      @mousedown.prevent="selectSuggestion(index)"
                      @mouseenter="activeSuggestionIndex = index"
                      :class="[
                        'px-4 py-3 cursor-pointer text-sm transition-colors border-b border-gray-100 last:border-0',
                        activeSuggestionIndex === index ? 'bg-red-50 text-red-700' : 'text-gray-700 hover:bg-gray-50'
                      ]"
                    >
                      <div class="font-medium">{{ suggestion.value }}</div>
                      <div class="text-xs text-gray-500 truncate">{{ suggestion.data.postal_code || 'Нет индекса' }}</div>
                    </li>
                  </ul>
                  
                  <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Подъезд</label>
                    <input 
                      v-model="formData.entrance"
                      @input="formatNumber($event, 'entrance')"
                      type="text"
                      maxlength="5"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-600 focus:border-red-600 transition"
                      placeholder="№"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Этаж</label>
                    <input 
                      v-model="formData.floor"
                      @input="formatNumber($event, 'floor')"
                      type="text"
                      maxlength="5"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-600 focus:border-red-600 transition"
                      placeholder="№"
                    >
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Домофон / Код</label>
                  <input 
                    v-model="formData.intercom"
                    @input="formatNumber($event, 'intercom')"
                    type="text"
                    maxlength="10"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-600 focus:border-red-600 transition"
                    placeholder="Код домофона"
                  >
                </div>
              </div>
            </div>

            <!-- Блок самовывоза -->
            <div v-if="formData.delivery === 'pickup'" class="bg-red-50 border border-red-200 rounded-lg p-4">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <div>
                  <p class="font-medium text-red-700">Адрес самовывоза:</p>
                  <p class="text-red-600">г. Иркутск, ул. Ленина, 5А</p>
                  <p class="text-sm text-red-700 mt-1">Режим работы: Пн-Пт 10:00–19:00, Сб 11:00–17:00</p>
                  <p class="text-xs text-red-600 mt-2">Готовность заказа: 10-14 рабочих дней.</p>
                </div>
              </div>
            </div>
          </section>

          <section>
            <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">Комментарий к заказу</h2>
            <textarea 
              v-model="formData.comment"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-1 focus:ring-red-600 focus:border-red-600 transition resize-none"
              placeholder="Пожелания..."
            ></textarea>
          </section>

          <section>
            <h2 class="text-lg font-semibold text-gray-900 mb-6 pb-4 border-b border-gray-200">Оплата</h2>
            <div class="space-y-3">
              <label 
                v-for="method in paymentMethods" 
                :key="method.id"
                :class="[
                  'flex items-center gap-4 p-4 border-2 rounded-lg cursor-pointer transition-colors',
                  formData.payment === method.id 
                    ? 'border-red-600 bg-red-50' 
                    : 'border-gray-300 hover:border-gray-400'
                ]"
              >
                <input 
                  type="radio" 
                  :value="method.id" 
                  v-model="formData.payment"
                  @change="validateField('payment')"
                  class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-600"
                >
                <div class="flex-1">
                  <span class="font-medium text-gray-900">{{ method.name }}</span>
                  <p class="text-xs text-gray-500 mt-1">{{ method.description }}</p>
                </div>
              </label>
            </div>
            <p v-if="errors.payment" class="mt-2 text-sm text-red-600">{{ errors.payment }}</p>
          </section>

        </div>

        <!-- Правая колонка: Заказ -->
        <div class="lg:col-span-1">
          <div class="sticky top-24 space-y-6">
            
            <div class="bg-gray-50 p-6 rounded-lg">
              <h3 class="font-semibold text-gray-900 mb-4">Ваш заказ</h3>
              <div class="space-y-4 mb-6">
                <div v-for="item in cartItems" :key="item.cartKey || item.id" class="flex gap-3">
                  <div class="w-16 h-16 bg-gray-200 flex-shrink-0 rounded overflow-hidden">
                    <img v-if="item.image" :src="getImageUrl(item.image)" class="w-full h-full object-cover">
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</p>
                    <p v-if="item.size" class="text-xs text-gray-500">Размер: {{ item.size }}</p>
                    <p class="text-xs text-gray-500">× {{ item.quantity }}</p>
                  </div>
                  <p class="text-sm font-medium text-gray-900">{{ formatPrice(item.price * item.quantity) }}</p>
                </div>
              </div>
              
              <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Товары:</span>
                  <span class="text-gray-900">{{ formatPrice(subtotal) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Доставка:</span>
                  <span class="text-gray-900">{{ deliveryPrice === 0 ? 'Бесплатно' : formatPrice(deliveryPrice) }}</span>
                </div>
                <div class="flex justify-between font-semibold text-lg pt-2 border-t border-gray-200">
                  <span class="text-gray-900">Итого:</span>
                  <span class="text-red-600">{{ formatPrice(total) }}</span>
                </div>
              </div>
            </div>

            <button 
              @click="processPayment"
              :disabled="isProcessing || !isFormValid"
              :class="[
                'w-full py-4 text-white font-semibold uppercase tracking-[0.15em] rounded-lg transition-colors flex items-center justify-center gap-2',
                isProcessing || !isFormValid ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-700 hover:bg-red-800'
              ]"
            >
              <span v-if="isProcessing" class="inline-block animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
              {{ isProcessing ? 'Обработка...' : `Оплатить ${formatPrice(total)}` }}
            </button>

            <p class="text-xs text-gray-500 text-center flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Защищённое соединение
            </p>

          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
// ============================================================================
// ИМПОРТЫ И ИНИЦИАЛИЗАЦИЯ
// ============================================================================
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCart } from '../composables/useCart';
import axios from 'axios';

const router = useRouter();
const { items: cartItems, totalPrice: cartTotal, clearCart } = useCart();

// ============================================================================
// КОНФИГУРАЦИЯ И КОНСТАНТЫ
// ============================================================================
const API_URL = 'http://127.0.0.1:8000/api';
const DADATA_API_KEY = '0e925574c874e72abe91d7dacda0619e41d72743';
const DADATA_URL = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';

// ============================================================================
// СОСТОЯНИЕ ФОРМЫ (РЕАКТИВНЫЕ ДАННЫЕ)
// ============================================================================
// Данные формы оформления заказа
const formData = ref({
  name: '',
  email: '', 
  phone: '',
  address: '',
  entrance: '',
  floor: '',
  intercom: '',
  comment: '',
  payment: 'card',
  delivery: 'pickup',
  cdekCity: '',
  cdekAddress: ''
});

// Объект ошибок валидации для каждого поля формы
const errors = ref({});

// Флаги состояния обработки и успешного завершения заказа
const isProcessing = ref(false);
const orderSuccess = ref(false);
const orderNumber = ref('');

// Данные сохранённых адресов пользователя
const userAddresses = ref([]);
const selectedAddressId = ref(null);
const showManualAddress = ref(false);

// ============================================================================
// ЛОГИКА ПОДСКАЗОК АДРЕСА (DADATA)
// ============================================================================
const addressInputRef = ref(null);
const addressSuggestions = ref([]);
const showSuggestions = ref(false);
const activeSuggestionIndex = ref(-1);
let debounceTimer = null;

// ============================================================================
// ЗАГРУЗКА ДАННЫХ ПОЛЬЗОВАТЕЛЯ
// ============================================================================
// Загрузка сохранённых адресов доставки из API
const loadUserAddresses = async () => {
  try {
    const token = localStorage.getItem('api_token');
    if (!token) return;
    
    const res = await axios.get(`${API_URL}/user/addresses`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    
    userAddresses.value = res.data?.data || res.data || [];
    
    // Автовыбор адреса по умолчанию при доставке по Иркутску
    if (userAddresses.value.length > 0 && formData.value.delivery === 'irkutsk') {
      const defaultAddr = userAddresses.value.find(addr => addr.is_default) || userAddresses.value[0];
      selectAddress(defaultAddr);
    }
  } catch (error) {
    console.error('Failed to load addresses:', error);
  }
};

// Выбор сохранённого адреса и заполнение полей формы
const selectAddress = (addr) => {
  selectedAddressId.value = addr.id;
  formData.value.address = `${addr.street}, ${addr.building}${addr.apartment ? ', кв. ' + addr.apartment : ''}`;
  formData.value.entrance = addr.entrance || '';
  formData.value.floor = addr.floor || '';
  formData.value.intercom = addr.intercom || '';
  showManualAddress.value = false;
  errors.value.address = '';
};

// Обработчик изменения способа доставки
const onDeliveryChange = () => {
  if (formData.value.delivery === 'irkutsk') {
    loadUserAddresses();
  }
  validateField('delivery');
};

// ============================================================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ФОРМАТИРОВАНИЯ
// ============================================================================
// Форматирование числовых полей (удаление нецифровых символов)
const formatNumber = (event, field) => {
  const value = event.target.value.replace(/\D/g, '');
  formData.value[field] = value;
};

// Форматирование номера телефона в международный формат +7 (XXX) XXX-XX-XX
const formatPhone = () => {
  let phone = formData.value.phone.replace(/\D/g, '');
  if (phone.startsWith('8')) phone = '7' + phone.slice(1);
  if (!phone.startsWith('7')) phone = '7' + phone;
  
  let formatted = '+7';
  if (phone.length > 1) formatted += ' (' + phone.slice(1, 4);
  if (phone.length >= 5) formatted += ') ' + phone.slice(4, 7);
  if (phone.length >= 8) formatted += '-' + phone.slice(7, 9);
  if (phone.length >= 10) formatted += '-' + phone.slice(9, 11);
  formData.value.phone = formatted;
};

// Форматирование цены с разделителями тысяч и символом рубля
const formatPrice = (value) => {
  const num = Number(value);
  return isNaN(num) ? '0 ₽' : `${num.toLocaleString('ru-RU')} ₽`;
};

// Построение полного URL для изображения товара
const getImageUrl = (image) => {
  if (!image) return '';
  if (image.startsWith('http')) return image;
  return `http://127.0.0.1:8000/storage/${image}`;
};

// ============================================================================
// СПРАВОЧНИКИ: СПОСОБЫ ДОСТАВКИ И ОПЛАТЫ
// ============================================================================
const deliveryMethods = [
  { id: 'pickup', name: 'Самовывоз', price: 0, description: 'Бесплатно из нашей мастерской в Иркутске' },
  { id: 'irkutsk', name: 'Доставка по Иркутску', price: 500, description: 'Курьерская доставка в пределах города' },
  { id: 'cdek', name: 'СДЭК', price: 1000, description: 'Доставка в любой город России через службу СДЭК' }
];

const paymentMethods = [
  { id: 'card', name: 'Банковская карта', description: 'Visa, Mastercard, МИР' },
  { id: 'cash', name: 'При получении', description: 'Оплата наличными курьеру или в пункте выдачи' },
  { id: 'sbp', name: 'СБП', description: 'Оплата по QR-коду' }
];

// ============================================================================
// ВЫЧИСЛЯЕМЫЕ СВОЙСТВА
// ============================================================================
// Промежуточная сумма заказа (без учёта доставки)
const subtotal = computed(() => cartTotal.value);

// Стоимость выбранного способа доставки
const deliveryPrice = computed(() => {
  const method = deliveryMethods.find(m => m.id === formData.value.delivery);
  return method ? method.price : 0;
});

// Итоговая сумма заказа с учётом доставки
const total = computed(() => subtotal.value + deliveryPrice.value);

// Вычисляемое свойство: общая валидность формы перед отправкой
const isFormValid = computed(() => {
  const { name, email, phone, delivery, payment } = formData.value;
  if (!name || name.trim().length < 2) return false;
  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return false;
  if (!phone || phone.replace(/\D/g, '').length !== 11) return false;
  if (!delivery || !payment) return false;
  if (delivery === 'irkutsk' && (!formData.value.address || formData.value.address.trim().length < 5)) return false;
  if (delivery === 'cdek' && (!formData.value.cdekCity || !formData.value.cdekAddress)) return false;
  return true;
});

// ============================================================================
// ВАЛИДАЦИЯ ФОРМЫ
// ============================================================================
// Валидация отдельного поля формы с установкой сообщения об ошибке
const validateField = (field) => {
  const value = formData.value[field];
  switch (field) {
    case 'name':
      errors.value.name = (!value || value.trim().length < 2) ? 'Введите имя (минимум 2 символа)' : '';
      break;
    case 'email':
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      errors.value.email = (!value) ? 'Email обязателен' : (!emailRegex.test(value) ? 'Введите корректный email' : '');
      break;
    case 'phone':
      const phoneDigits = value.replace(/\D/g, '');
      errors.value.phone = (!value) ? 'Телефон обязателен' : (phoneDigits.length !== 11 ? 'Введите полный номер телефона' : '');
      break;
    case 'delivery':
      errors.value.delivery = (!value) ? 'Выберите способ доставки' : '';
      break;
    case 'address':
      if (formData.value.delivery === 'irkutsk' && (!value || value.trim().length < 5)) {
        errors.value.address = 'Введите полный адрес доставки';
      } else {
        errors.value.address = '';
      }
      break;
    case 'cdekCity':
      if (formData.value.delivery === 'cdek' && (!value || value.trim().length < 2)) {
        errors.value.cdekCity = 'Укажите город';
      } else {
        errors.value.cdekCity = '';
      }
      break;
    case 'cdekAddress':
      if (formData.value.delivery === 'cdek' && (!value || value.trim().length < 2)) {
        errors.value.cdekAddress = 'Укажите адрес пункта выдачи';
      } else {
        errors.value.cdekAddress = '';
      }
      break;
    case 'payment':
      errors.value.payment = (!value) ? 'Выберите способ оплаты' : '';
      break;
  }
};

// Комплексная валидация всей формы перед отправкой
const validateForm = () => {
  errors.value = {};
  let isValid = true;
  ['name', 'email', 'phone', 'delivery', 'payment'].forEach(validateField);
  
  if (Object.values(errors.value).some(e => e)) isValid = false;
  
  if (formData.value.delivery === 'irkutsk') {
    validateField('address');
    if (errors.value.address) isValid = false;
  }
  if (formData.value.delivery === 'cdek') {
    validateField('cdekCity');
    validateField('cdekAddress');
    if (errors.value.cdekCity || errors.value.cdekAddress) isValid = false;
  }
  return isValid;
};

// ============================================================================
// ИНТЕГРАЦИЯ С DADATA: ПОИСК АДРЕСА
// ============================================================================
// Запрос подсказок адресов к API DaData с ограничением по городу Иркутск
const fetchAddressSuggestions = async (query) => {
  if (!query || query.length < 3) {
    addressSuggestions.value = [];
    showSuggestions.value = false;
    return;
  }

  try {
    const response = await axios.post(DADATA_URL, 
      { 
        query: query,
        locations: [{ city: "Иркутск" }] 
      },
      {
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': `Token ${DADATA_API_KEY}`
        }
      }
    );
    
    addressSuggestions.value = response.data.suggestions || [];
    showSuggestions.value = addressSuggestions.value.length > 0;
    activeSuggestionIndex.value = -1;
  } catch (error) {
    console.error('DaData error:', error);
    addressSuggestions.value = [];
    showSuggestions.value = false;
  }
};

// Обработчик ввода адреса с дебаунсом для оптимизации запросов к API
const handleAddressInput = (event) => {
  const query = event.target.value;
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchAddressSuggestions(query);
  }, 300);
};

// Скрытие списка подсказок с задержкой для корректной обработки кликов
const hideSuggestions = () => {
  setTimeout(() => {
    showSuggestions.value = false;
  }, 200);
};

// Навигация по списку подсказок с клавиатуры (стрелки вверх/вниз)
const navigateSuggestions = (direction) => {
  if (!showSuggestions.value || addressSuggestions.value.length === 0) return;
  
  let newIndex = activeSuggestionIndex.value + direction;
  if (newIndex < 0) newIndex = addressSuggestions.value.length - 1;
  if (newIndex >= addressSuggestions.value.length) newIndex = 0;
  
  activeSuggestionIndex.value = newIndex;
};

// Выбор подсказки и автозаполнение полей формы структурированными данными
const selectSuggestion = (index) => {
  if (index === -1 || index >= addressSuggestions.value.length) return;
  
  const suggestion = addressSuggestions.value[index];
  const data = suggestion.data;
  
  // Формирование полной строки адреса из компонентов DaData
  const streetPart = data.street_with_type || data.settlement_with_type || '';
  const housePart = data.house_type ? `${data.house_type} ${data.house}` : (data.house || '');
  const flatPart = data.flat_type ? `${data.flat_type} ${data.flat}` : (data.flat ? `кв ${data.flat}` : '');
  
  let fullAddress = [streetPart, housePart].filter(Boolean).join(', ');
  if (flatPart) fullAddress += `, ${flatPart}`;
  
  formData.value.address = fullAddress;
  
  // Автозаполнение дополнительных полей при наличии данных
  if (data.block) formData.value.entrance = data.block;
  
  // Сброс состояния подсказок и валидация поля
  showSuggestions.value = false;
  activeSuggestionIndex.value = -1;
  validateField('address');
};

// ============================================================================
// ОБРАБОТКА ОФОРМЛЕНИЯ ЗАКАЗА
// ============================================================================
// Основная функция отправки заказа на сервер
const processPayment = async () => {
  // Предварительная валидация формы
  if (!validateForm()) {
    const firstError = document.querySelector('.border-red-500');
    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    alert('Пожалуйста, исправьте ошибки в форме');
    return;
  }
  
  // Проверка наличия товаров в корзине
  if (cartItems.value.length === 0) {
    alert('Корзина пуста');
    return;
  }

  isProcessing.value = true;

  try {
    const token = localStorage.getItem('api_token');
    if (!token) {
      alert('Пожалуйста, войдите в аккаунт');
      router.push('/login');
      return;
    }

    // Формирование массива товаров для отправки
    const items = cartItems.value.map(item => {
      const productId = item.product_id || item.id; 
      return {
        product_id: productId,
        quantity: item.quantity,
        price: item.price,
        size: item.size || null,
      };
    });

    // Подготовка данных доставки в зависимости от выбранного метода
    let shippingData = {
      delivery_method: formData.value.delivery,
      delivery_price: deliveryPrice.value,
    };

    if (formData.value.delivery === 'cdek') {
      shippingData = { ...shippingData, cdek_city: formData.value.cdekCity, cdek_address: formData.value.cdekAddress };
    } else if (formData.value.delivery === 'irkutsk') {
      shippingData = { ...shippingData, address: formData.value.address, entrance: formData.value.entrance || null, floor: formData.value.floor || null, intercom: formData.value.intercom || null };
    } else if (formData.value.delivery === 'pickup') {
      shippingData = { ...shippingData, pickup_address: 'г. Иркутск, ул. Ленина, 5А' };
    }

    // Отправка запроса на оформление заказа
    const response = await axios.post(`${API_URL}/checkout`, {
      items: items,
      ...shippingData,
      name: formData.value.name,
      email: formData.value.email,
      phone: formData.value.phone.replace(/\D/g, ''),
      payment_method: formData.value.payment,
      comment: formData.value.comment || null,
    }, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
      }
    });

    // Обработка успешного ответа: сохранение номера заказа и очистка корзины
    orderNumber.value = response.data.order?.order_number || `ZIMA-${Date.now().toString(36).toUpperCase()}`;
    clearCart();
    orderSuccess.value = true;
    window.scrollTo({ top: 0, behavior: 'smooth' });

  } catch (error) {
    // Обработка ошибок API и сетевых сбоев
    console.error('Checkout error:', error);
    const apiErrors = error.response?.data?.errors || {}; 
    const errorMessages = Object.values(apiErrors).flat().join(', ');
    const message = error.response?.data?.message || errorMessages || error.message || 'Неизвестная ошибка';
    alert('Ошибка оформления заказа: ' + message);

    // Автоматический выход при ошибке авторизации
    if (error.response?.status === 401) {
      localStorage.removeItem('api_token');
      router.push('/login');
    }
  } finally {
    isProcessing.value = false;
  }
};

// Сброс состояния формы и перенаправление на главную страницу
const resetOrder = () => {
  orderSuccess.value = false;
  formData.value = { name: '', email: '', phone: '', address: '', entrance: '', floor: '', intercom: '', comment: '', payment: 'card', delivery: 'pickup', cdekCity: '', cdekAddress: '' };
  errors.value = {};
  userAddresses.value = [];
  selectedAddressId.value = null;
  showManualAddress.value = false;
  addressSuggestions.value = [];
  showSuggestions.value = false;
  router.push('/');
};

// ============================================================================
// ЖИЗНЕННЫЙ ЦИКЛ КОМПОНЕНТА
// ============================================================================
onMounted(() => {
  // Перенаправление в каталог при пустой корзине
  if (cartItems.value.length === 0) router.push('/catalog');
});
</script>

<style scoped>
/* Глобальные переходы для интерактивных элементов */
* { transition: border-color 0.2s, background-color 0.2s, color 0.2s; }

/* Убираем стандартный контур фокуса для полей ввода */
input:focus, textarea:focus { outline: none; }

/* Стилизация радио-кнопок */
input[type="radio"] { accent-color: #dc2626; width: 1.25rem; height: 1.25rem; cursor: pointer; }

/* Анимация выделения выбранного варианта доставки */
label:has(input[type="radio"]:checked) { animation: pulse 0.3s ease-out; }
@keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.01); } 100% { transform: scale(1); } }

/* Анимация ошибки валидации: покраснение фона и покачивание поля */
input.border-red-500, textarea.border-red-500 { animation: shake 0.3s ease-in-out; background-color: #fef2f2; }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }

/* Утилитарные классы для состояний ошибок */
.bg-red-50 { background-color: #ffedeb; }
.border-red-200 { border-color: #d80000; }
.text-red-700 { color: #b91c1c; }
.text-red-600 { color: #dc2626; }

/* Скрытие стандартных стрелок у полей ввода числа */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  appearance: none;
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>