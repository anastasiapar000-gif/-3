<template>
  <!-- Корневой контейнер страницы управления пользователями -->
  <div>
    
    <!-- Заголовок страницы -->
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Пользователи</h1>

    <!-- Блок поиска и фильтров: позволяет фильтровать список пользователей по имени, email и роли -->
    <div class="bg-white rounded-xl shadow p-4 mb-6">
      <div class="flex flex-col sm:flex-row gap-4">
        
        <!-- Поле поиска: фильтрация по имени, логину или email -->
        <div class="flex-1">
          <label class="block text-sm font-medium text-gray-700 mb-1">Поиск</label>
          <div class="relative">
            <input 
              v-model="searchQuery"
              type="text"
              placeholder="Поиск по имени или email..."
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
            >
            <!-- Иконка поиска (SVG) -->
            <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <!-- Кнопка очистки поиска: показывается только при введённом запросе -->
            <button 
              v-if="searchQuery"
              @click="searchQuery = ''"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Фильтр по роли пользователя: покупатель или администратор -->
        <div class="sm:w-48">
          <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
          <select 
            v-model="roleFilter"
            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition bg-white"
          >
            <option value="">Все роли</option>
            <option value="buyer">Покупатель</option>
            <option value="admin">Админ</option>
          </select>
        </div>

        <!-- Кнопка сброса всех фильтров: активируется только при наличии активных фильтров -->
        <div class="sm:w-auto flex items-end">
          <button 
            @click="clearFilters"
            :disabled="!searchQuery && !roleFilter"
            class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Сбросить
          </button>
        </div>
      </div>

      <!-- Статистика фильтрации: показывает количество найденных пользователей -->
      <div v-if="searchQuery || roleFilter" class="mt-3 flex items-center gap-2 text-sm text-gray-600">
        <span>Найдено:</span>
        <span class="font-semibold text-gray-900">{{ filteredUsers.length }}</span>
        <span>из</span>
        <span class="font-semibold text-gray-900">{{ users.length }}</span>
        <button @click="clearFilters" class="text-red-600 hover:text-red-700 ml-2">
          Сбросить фильтры
        </button>
      </div>
    </div>

    <!-- Индикатор загрузки: показывается во время выполнения асинхронного запроса к API -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-red-600 border-t-transparent"></div>
      <p class="mt-3 text-gray-600">Загрузка пользователей...</p>
    </div>

    <!-- Сообщение об ошибке: показывается при неудачном запросе к серверу -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
      {{ error }}
    </div>

    <!-- Таблица пользователей: список с данными и действиями -->
    <div v-else class="bg-white rounded-xl shadow overflow-hidden">
      <table class="w-full">
        <!-- Заголовки столбцов таблицы -->
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Имя</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Заказов</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Роль</th>
            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase">Действия</th>
          </tr>
        </thead>
        
        <!-- Тело таблицы: список пользователей с поддержкой разных форматов данных -->
        <tbody class="divide-y divide-gray-200">
          <tr v-for="u in filteredUsers" :key="getKey(u)" class="hover:bg-gray-50">
            <!-- ID пользователя: поддержка полей user_id и id -->
            <td class="px-6 py-4 text-sm text-gray-600">{{ u.user_id || u.id || '—' }}</td>
            
            <!-- Имя пользователя: поддержка полей login, name, full_name -->
            <td class="px-6 py-4 font-medium">
              {{ u.login || u.name || u.full_name || '—' }}
              <span v-if="u.is_admin" class="ml-2 text-xs text-red-600">(админ)</span>
            </td>
            
            <!-- Email пользователя -->
            <td class="px-6 py-4 text-gray-600">{{ u.email || '—' }}</td>
            
            <!-- Количество заказов пользователя -->
            <td class="px-6 py-4 text-gray-600">{{ u.orders_count || 0 }}</td>
            
            <!-- Роль пользователя: цветной бейдж в зависимости от значения -->
            <td class="px-6 py-4">
              <span :class="u.role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'" 
                    class="px-3 py-1 rounded-full text-xs font-medium uppercase">
                {{ u.role === 'admin' ? 'Админ' : 'Покупатель' }}
              </span>
            </td>
            
            <!-- Кнопка удаления пользователя -->
            <td class="px-6 py-4 text-right">
              <button 
                @click="deleteUser(getId(u))" 
                class="text-red-600 hover:text-red-800 text-sm font-medium"
              >
                Удалить
              </button>
            </td>
          </tr>
          
          <!-- Сообщение при пустом результате: разные тексты для фильтров и полного отсутствия данных -->
          <tr v-if="!loading && filteredUsers.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
              {{ searchQuery || roleFilter ? 'Ничего не найдено по заданным фильтрам' : 'Пользователи не найдены' }}
            </td>
          </tr>
        </tbody>
      </table>
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

// Массив всех пользователей (загружается с сервера)
const users = ref([]);

// Флаг загрузки данных с сервера
const loading = ref(true);

// Текст ошибки для отображения пользователю
const error = ref('');

// Поисковый запрос для фильтрации по имени или email
const searchQuery = ref('');

// Значение фильтра по роли пользователя
const roleFilter = ref('');

// === COMPUTED: Вычисляемые свойства ===

// Фильтрация списка пользователей по поисковому запросу и роли
const filteredUsers = computed(() => {
  // Исключаем пустые и неопределённые значения из массива
  let result = users.value.filter(u => u !== null && u !== undefined);
  
  // Фильтрация по поисковому запросу: поиск по логину, имени или email (без учёта регистра)
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim();
    result = result.filter(u => {
      const login = (u.login || u.name || u.full_name || '').toLowerCase();
      const email = (u.email || '').toLowerCase();
      return login.includes(query) || email.includes(query);
    });
  }
  
  // Фильтрация по роли: оставляем только пользователей с выбранной ролью
  if (roleFilter.value) {
    result = result.filter(u => u.role === roleFilter.value);
  }
  
  return result;
});

// === МЕТОДЫ: Обработчики действий ===

// Очистка всех фильтров: сбрасывает поисковый запрос и фильтр по роли
const clearFilters = () => {
  searchQuery.value = '';
  roleFilter.value = '';
};

// Получение ID пользователя: поддержка разных форматов данных от сервера (user_id или id)
const getId = (user) => user?.user_id || user?.id || null;

// Получение уникального ключа для v-for: использует ID или генерирует случайную строку
const getKey = (user) => {
  const id = getId(user);
  return id || Math.random().toString(36).substr(2, 9);
};

// Загрузка списка пользователей с сервера
const load = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    const token = localStorage.getItem('api_token');
    
    const response = await axios.get(`${API_URL}/admin/users`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    // Нормализация ответа: поддержка разных форматов данных от сервера
    if (Array.isArray(response.data)) {
      users.value = response.data;
    } else if (response.data?.data && Array.isArray(response.data.data)) {
      users.value = response.data.data;
    } else {
      users.value = [];
    }
    
  } catch (err) {
    console.error('Ошибка загрузки пользователей:', err);
    // Обработка ошибок по кодам статуса HTTP
    error.value = err.response?.status === 403 
      ? 'Нет доступа. Требуется роль администратора.' 
      : 'Ошибка загрузки пользователей';
    users.value = [];
  } finally {
    // Сброс флага загрузки независимо от результата
    loading.value = false;
  }
};

// Удаление пользователя: запрос подтверждения и отправка DELETE-запроса
const deleteUser = async (id) => {
  // Проверка наличия ID и подтверждение действия пользователем
  if (!id || !confirm('Удалить пользователя?')) return;
  
  try {
    const token = localStorage.getItem('api_token');
    
    await axios.delete(`${API_URL}/admin/users/${id}`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    
    // Перезагрузка списка после успешного удаления
    await load();
    
  } catch (err) {
    console.error('Ошибка удаления пользователя:', err);
    // Отображение сообщения об ошибке с деталями от сервера
    alert('Ошибка: ' + (err.response?.data?.message || 'Не удалось удалить'));
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ ===

// Загрузка данных при монтировании компонента
onMounted(load);
</script>