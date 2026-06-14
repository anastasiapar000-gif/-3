<template>
  <!-- Корневой контейнер страницы с отступами между секциями -->
  <div class="space-y-6">
    
    <!-- Заголовок страницы и кнопка добавления категории -->
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 tracking-wide">Категории</h1>
      <button 
        @click="openModal()" 
        class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50"
        :disabled="loading"
      >
        + Добавить
      </button>
    </div>

    <!-- Блок поиска: фильтр по названию или slug -->
    <div class="bg-white rounded-xl shadow p-4">
      <input 
        v-model="search" 
        type="text" 
        placeholder="Поиск по названию..." 
        class="w-full max-w-md px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition"
      >
      <!-- Статистика поиска: показывается только при введённом запросе -->
      <p v-if="search" class="text-sm text-gray-500 mt-2">
        Найдено: <span class="font-semibold">{{ filteredCategories.length }}</span> из {{ categories.length }}
      </p>
    </div>

    <!-- Индикатор загрузки: показывается во время выполнения API-запроса -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
      <p class="text-gray-500 mt-2">Загрузка категорий...</p>
    </div>

    <!-- Блок ошибки: показывается при неудачном запросе к серверу -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      <div class="flex items-center justify-between">
        <span>{{ error }}</span>
        <button @click="load" class="text-sm underline hover:no-underline">Повторить</button>
      </div>
    </div>

    <!-- Таблица категорий: показывается при успешной загрузке данных -->
    <div v-else class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full">
        <!-- Заголовки таблицы -->
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Название</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Slug</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Товаров</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <!-- Тело таблицы: список категорий -->
        <tbody class="divide-y divide-gray-200">
          <tr v-for="cat in filteredCategories" :key="cat.id" class="hover:bg-gray-50 transition">
            <td class="px-6 py-4 font-medium text-gray-900">{{ cat.name }}</td>
            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ cat.slug }}</td>
            <td class="px-6 py-4">
              <!-- Ссылка на фильтр товаров по категории -->
              <router-link 
                :to="`/admin/products?category=${cat.id}`" 
                class="text-blue-600 hover:text-blue-800 hover:underline font-medium"
              >
                {{ cat.products_count }} шт.
              </router-link>
            </td>
            <td class="px-6 py-4 text-right space-x-3">
              <!-- Кнопка редактирования: открывает модальное окно с данными категории -->
              <button 
                @click="editCategory(cat)" 
                class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline"
              >
                Редактировать
              </button>
              <!-- Кнопка удаления: вызывает подтверждение и DELETE-запрос -->
              <button 
                @click="deleteCategory(cat.id)" 
                class="text-red-600 hover:text-red-800 text-sm font-medium hover:underline"
              >
                Удалить
              </button>
            </td>
          </tr>
          <!-- Сообщение при пустом результате поиска или отсутствии категорий -->
          <tr v-if="filteredCategories.length === 0">
            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
              <div v-if="search">
                Категории не найдены по запросу "{{ search }}"
              </div>
              <div v-else>
                Категории не найдены
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Модальное окно: форма создания/редактирования категории -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="showModal = false">
      <!-- Контент модального окна: stop предотвращает закрытие при клике внутри -->
      <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md" @click.stop>
        <h3 class="text-lg font-bold mb-4 text-gray-900">
          {{ editing ? 'Редактировать' : 'Новая' }} категория
        </h3>
        
        <!-- Поле ввода названия категории с привязкой к форме и обработкой ошибок -->
        <input 
          v-model="form.name" 
          class="w-full px-4 py-2 border rounded-lg mb-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none transition" 
          :class="{'border-red-500 bg-red-50': errors.name}"
          placeholder="Название категории"
          :disabled="saving"
          @keyup.enter="saveCategory"
          @input="clearError('name')"
        >
        
        <!-- Сообщение об ошибке валидации для поля name -->
        <p v-if="errors.name" class="text-sm text-red-600 mb-4">{{ errors.name }}</p>
        
        <!-- Кнопки действий: отмена и сохранение -->
        <div class="flex justify-end gap-3">
          <button 
            @click="showModal = false" 
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition disabled:opacity-50"
            :disabled="saving"
          >
            Отмена
          </button>
          <button 
            @click="saveCategory" 
            class="px-4 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'Сохранение...' : 'Сохранить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивных хуков Vue и библиотеки для HTTP-запросов
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Базовый URL API для административной части
const API_URL = 'http://127.0.0.1:8000/api';

// === STATE: Реактивные переменные состояния компонента ===

// Массив всех категорий (загружается с сервера)
const categories = ref([]);

// Поисковый запрос для фильтрации списка
const search = ref('');

// Флаг видимости модального окна
const showModal = ref(false);

// Объект редактируемой категории (null — режим создания)
const editing = ref(null);

// Флаг загрузки данных с сервера
const loading = ref(true);

// Флаг выполнения операции сохранения (блокирует кнопки)
const saving = ref(false);

// Текст общей ошибки (для отображения в блоке ошибки)
const error = ref('');

// Объект ошибок валидации по полям формы (например, { name: '...' })
const errors = ref({});

// Объект формы: данные для создания/редактирования категории
const form = ref({ name: '' });

// === ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Формирует заголовки для авторизованных API-запросов
const getAuthHeaders = () => {
  const token = localStorage.getItem('api_token');
  return {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  };
};

// === API-МЕТОДЫ ===

// Загрузка списка категорий с сервера
const load = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const { data } = await axios.get(`${API_URL}/admin/categories`, {
      headers: getAuthHeaders()
    });
    
    // Нормализация ответа: поддержка разных форматов данных от сервера
    categories.value = data.data || data || [];
    
  } catch (err) {
    console.error('Ошибка загрузки категорий:', err);
    
    // Обработка ошибок по кодам статуса HTTP
    if (err.response?.status === 401) {
      error.value = 'Сессия истекла. Перезайдите в систему.';
      // Очистка токенов и перенаправление на страницу входа
      localStorage.removeItem('api_token');
      localStorage.removeItem('user');
      setTimeout(() => window.location.href = '/admin/login', 2000);
    } else if (err.response?.status === 403) {
      error.value = 'Доступ запрещён. Требуется роль администратора.';
    } else if (err.response?.status === 500) {
      error.value = 'Ошибка сервера. Попробуйте позже.';
    } else {
      error.value = 'Не удалось загрузить категории';
    }
  } finally {
    // Сброс флага загрузки независимо от результата
    loading.value = false;
  }
};

// === ВЫЧИСЛЯЕМЫЕ СВОЙСТВА ===

// Фильтрация категорий по поисковому запросу (по name и slug)
const filteredCategories = computed(() => {
  if (!search.value) return categories.value;
  const q = search.value.toLowerCase().trim();
  return categories.value.filter(c => 
    c.name?.toLowerCase().includes(q) || 
    c.slug?.toLowerCase().includes(q)
  );
});

// === МЕТОДЫ ФОРМЫ ===

// Очистка ошибки валидации для конкретного поля формы
const clearError = (field) => {
  if (errors.value[field]) {
    errors.value[field] = '';
  }
};

// Открытие модального окна: режим создания или редактирования
const openModal = (cat = null) => {
  editing.value = cat;
  // Заполнение формы данными категории или сброс для новой
  form.value = cat ? { 
    name: cat.name || '',
    slug: cat.slug || '',
    description: cat.description || ''
  } : { name: '' };
  // Сброс ошибок при открытии окна
  errors.value = {};
  showModal.value = true;
  
  // Автофокус на поле ввода после открытия модального окна
  setTimeout(() => {
    const input = document.querySelector('input[placeholder="Название категории"]');
    if (input) input.focus();
  }, 100);
};

// Сохранение категории: создание или обновление через API
const saveCategory = async () => {
  // Простая валидация на фронтенде перед отправкой
  if (!form.value.name.trim()) {
    errors.value.name = 'Название категории обязательно';
    return;
  }
  if (form.value.name.length < 2) {
    errors.value.name = 'Название должно содержать минимум 2 символа';
    return;
  }
  
  saving.value = true;
  errors.value = {}; // Сброс ошибок перед новым запросом
  
  try {
    if (editing.value) {
      // РЕДАКТИРОВАНИЕ: PUT-запрос с указанием ID категории
      await axios.put(
        `${API_URL}/admin/categories/${editing.value.id}`, 
        { name: form.value.name.trim() },
        { headers: getAuthHeaders() }
      );
    } else {
      // СОЗДАНИЕ: POST-запрос без указания ID
      await axios.post(
        `${API_URL}/admin/categories`, 
        { name: form.value.name.trim() },
        { headers: getAuthHeaders() }
      );
    }
    
    // Успех: закрываем окно и перезагружаем список
    showModal.value = false;
    await load();
    
  } catch (e) {
    console.error('Ошибка сохранения:', e);
    
    // Обработка ошибок валидации от сервера (статус 422)
    if (e.response?.status === 422 && e.response?.data?.errors) {
      const serverErrors = e.response.data.errors;
      
      // Заполнение ошибок по полям формы
      if (serverErrors.name) {
        errors.value.name = Array.isArray(serverErrors.name) 
          ? serverErrors.name[0] 
          : serverErrors.name;
      }
      
      // Показ уведомления об ошибке
      alert('Ошибка валидации: ' + (errors.value.name || 'Некорректные данные'));
      
    } else if (e.response?.status === 401) {
      // Сессия истекла
      alert('Сессия истекла. Перезайдите.');
      localStorage.removeItem('api_token');
      window.location.href = '/admin/login';
      
    } else if (e.response?.status === 404) {
      // Категория не найдена (при редактировании)
      alert('Категория не найдена');
      
    } else {
      // Общая ошибка сервера или сети
      const msg = e.response?.data?.message || e.message || 'Ошибка при сохранении';
      alert(msg);
    }
  } finally {
    // Сброс флага сохранения
    saving.value = false;
  }
};

// Обработчик кнопки "Редактировать": открывает модальное окно с данными категории
const editCategory = (cat) => {
  openModal(cat);
};

// Удаление категории: запрос подтверждения и отправка DELETE-запроса
const deleteCategory = async (id) => {
  const category = categories.value.find(c => c.id === id);
  const categoryName = category?.name || 'эту категорию';
  
  // Подтверждение удаления пользователем
  if (!confirm(`Вы уверены, что хотите удалить "${categoryName}"?\n\nЭто действие нельзя отменить!`)) {
    return;
  }
  
  try {
    await axios.delete(`${API_URL}/admin/categories/${id}`, {
      headers: getAuthHeaders()
    });
    
    // Успех: перезагружаем список категорий
    await load();
    
  } catch (e) { 
    console.error('Ошибка удаления:', e);
    
    // Обработка ошибок по кодам статуса
    if (e.response?.status === 422) {
      // Нельзя удалить категорию, в которой есть товары
      const msg = e.response?.data?.message || 'В категории есть товары. Сначала удалите их.';
      alert('Невозможно удалить: ' + msg);
    } else if (e.response?.status === 404) {
      alert('Категория не найдена');
    } else {
      const msg = e.response?.data?.message || 'Ошибка при удалении';
      alert(msg);
    }
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ ===

// Загрузка данных при монтировании компонента
onMounted(load);
</script>