<template>
  <div class="space-y-6">
    <!-- Заголовок и кнопка обновления -->
    <div class="flex justify-between items-center">
      <h1 class="text-3xl font-bold text-gray-900 tracking-wide">Сообщения с сайта</h1>
      <button 
        @click="loadMessages" 
        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium transition"
      >
        Обновить
      </button>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-xl shadow border border-gray-100">
        <p class="text-sm text-gray-500">Всего сообщений</p>
        <p class="text-2xl font-bold">{{ totalMessages }}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow border border-gray-100">
        <p class="text-sm text-gray-500">Новые</p>
        <p class="text-2xl font-bold text-red-600">{{ unreadCount }}</p>
      </div>
      <div class="bg-white p-4 rounded-xl shadow border border-gray-100">
        <p class="text-sm text-gray-500">Прочитано</p>
        <p class="text-2xl font-bold text-green-600">{{ readCount }}</p>
      </div>
    </div>

    <!-- Таблица сообщений -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <!-- Загрузка -->
      <div v-if="loading" class="p-8 text-center text-gray-500">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-red-600 mr-2"></div>
        Загрузка...
      </div>

      <!-- Пусто -->
      <div v-else-if="!messages || messages.length === 0" class="p-8 text-center text-gray-500">
        Сообщений пока нет
      </div>

      <!-- Таблица -->
      <div v-else class="overflow-x-auto">
        <table class="w-full min-w-[1000px]">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Имя</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Контакт</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Сообщение</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Дата</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Статус</th>
              <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase w-48">Действия</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="msg in messages" :key="msg.id" :class="{'bg-yellow-50': !msg.is_read}">
              <td class="px-6 py-4 font-medium text-gray-900">{{ msg.name }}</td>
              <td class="px-6 py-4 text-gray-600 text-sm">{{ msg.contact }}</td>
              <td class="px-6 py-4 text-gray-600 text-sm max-w-xs truncate" :title="msg.message">
                {{ msg.message }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                {{ formatDate(msg.created_at) }}
              </td>
              <td class="px-6 py-4">
                <span v-if="msg.is_read" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">
                  Прочитано
                </span>
                <span v-else class="px-2 py-1 text-xs bg-red-100 text-red-600 rounded-full font-medium">
                  Новое
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex justify-center gap-2">
                  <button 
                    @click="viewMessage(msg)" 
                    class="px-4 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 transition"
                  >
                    Открыть
                  </button>
                  <button 
                    @click="deleteMessage(msg.id)" 
                    class="px-4 py-1.5 text-sm font-medium text-red-600 hover:text-red-800 transition"
                  >
                    Удалить
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Пагинация -->
      <div v-if="pagination && messages && messages.length > 0" class="px-6 py-4 border-t flex justify-between items-center">
        <p class="text-sm text-gray-500">
          Показано {{ pagination.from }}–{{ pagination.to }} из {{ pagination.total }}
        </p>
        <div class="flex gap-2">
          <button 
            v-if="pagination.prev_page_url"
            @click="loadPage(pagination.current_page - 1)"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50"
          >
            Назад
          </button>
          <button 
            v-if="pagination.next_page_url"
            @click="loadPage(pagination.current_page + 1)"
            class="px-3 py-1 text-sm border rounded hover:bg-gray-50"
          >
            Вперёд
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно просмотра сообщения -->
    <div v-if="selectedMessage" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click="closeModal">
      <div class="bg-white rounded-2xl p-6 max-w-2xl w-full mx-4 shadow-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex justify-between items-start mb-4">
          <h3 class="text-lg font-bold text-gray-900">Сообщение от {{ selectedMessage.name }}</h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">×</button>
        </div>
        
        <div class="space-y-3 mb-6">
          <div>
            <span class="text-sm font-medium text-gray-600">Контакт:</span>
            <p class="text-sm text-gray-900 mt-1 break-all">{{ selectedMessage.contact }}</p>
          </div>
          <div>
            <span class="text-sm font-medium text-gray-600">Дата:</span>
            <p class="text-sm text-gray-400 mt-1">{{ formatDate(selectedMessage.created_at) }}</p>
          </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
          <p class="text-sm text-gray-500 mb-2 font-medium">Текст сообщения:</p>
          <p class="text-gray-700 whitespace-pre-wrap break-words max-w-full overflow-wrap-anywhere">{{ selectedMessage.message }}</p>
        </div>

        <div class="flex justify-end gap-3">
          <button 
            @click="closeModal" 
            class="px-6 py-2 text-gray-700 font-medium hover:bg-gray-100 rounded-lg transition"
          >
            Закрыть
          </button>
          
          <!-- Кнопка "Прочитано" внутри модального окна (только для непрочитанных) -->
          <button 
            v-if="!selectedMessage.is_read"
            @click="markAsReadAndClose(selectedMessage.id)"
            class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition"
          >
            Прочитано
          </button>
          
          <button 
            @click="deleteFromModal(selectedMessage.id)"
            class="px-6 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition"
          >
            Удалить
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// Инициализация данных
const messages = ref([]);
const loading = ref(true);
const selectedMessage = ref(null);
const pagination = ref(null);

// Загрузка сообщений с сервера
const loadMessages = async (page = 1) => {
  loading.value = true;
  try {
    const token = localStorage.getItem('api_token');
    const response = await axios.get('http://127.0.0.1:8000/api/admin/contact-messages', {
      headers: { 'Authorization': `Bearer ${token}` },
      params: { page }
    });

    // Нормализация ответа Laravel
    messages.value = response.data.data || response.data || [];
    pagination.value = {
      current_page: response.data.current_page || 1,
      total: response.data.total || 0,
      from: response.data.from || 0,
      to: response.data.to || 0,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url,
    };
  } catch (error) {
    console.error('Load messages error:', error);
    alert('Ошибка загрузки сообщений');
    messages.value = [];
    pagination.value = null;
  } finally {
    loading.value = false;
  }
};

// Форматирование даты
const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Отметить как прочитанное
const markAsRead = async (id) => {
  try {
    const token = localStorage.getItem('api_token');
    await axios.put(`http://127.0.0.1:8000/api/admin/contact-messages/${id}/read`, {}, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    await loadMessages(); // перезагружаем список
  } catch (error) {
    console.error('Mark as read error:', error);
  }
};

// Отметить как прочитанное и закрыть модалку
const markAsReadAndClose = async (id) => {
  await markAsRead(id);
  closeModal();
};

// Удалить сообщение
const deleteMessage = async (id) => {
  if (!confirm('Удалить это сообщение?')) return;

  try {
    const token = localStorage.getItem('api_token');
    await axios.delete(`http://127.0.0.1:8000/api/admin/contact-messages/${id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    await loadMessages();
  } catch (error) {
    console.error('Delete error:', error);
  }
};

// Удалить из модального окна
const deleteFromModal = async (id) => {
  if (!confirm('Удалить это сообщение?')) return;
  
  try {
    const token = localStorage.getItem('api_token');
    await axios.delete(`http://127.0.0.1:8000/api/admin/contact-messages/${id}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    closeModal();
    await loadMessages();
  } catch (error) {
    alert('Ошибка удаления');
  }
};

// Открыть модальное окно просмотра
const viewMessage = (msg) => {
  selectedMessage.value = msg;
};

// Закрыть модальное окно
const closeModal = () => {
  selectedMessage.value = null;
};

// Переход на страницу пагинации
const loadPage = (page) => {
  loadMessages(page);
};

// Вычисляемые свойства для статистики
const totalMessages = computed(() => pagination.value?.total || 0);
const unreadCount = computed(() => {
  if (!messages.value || !Array.isArray(messages.value)) return 0;
  return messages.value.filter(m => !m.is_read).length;
});
const readCount = computed(() => {
  if (!messages.value || !Array.isArray(messages.value)) return 0;
  return messages.value.filter(m => m.is_read).length;
});

// Загружаем данные при монтировании
onMounted(loadMessages);
</script>

<style scoped>
/* Для переноса длинных слов */
.overflow-wrap-anywhere {
  overflow-wrap: anywhere;
  word-break: break-word;
}
</style>