<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900">Изображения товара</h3>
    
    <!-- Текущие изображения -->
    <div v-if="images.length > 0" class="grid grid-cols-4 gap-4">
      <div 
        v-for="image in images" 
        :key="image.id"
        class="relative group aspect-square bg-gray-100 rounded-lg overflow-hidden border-2"
        :class="image.is_main ? 'border-red-600' : 'border-gray-200'"
      >
        <img 
          :src="image.url" 
          :alt="image.alt_text"
          class="w-full h-full object-cover"
        >
        
        <!-- Бейдж главного изображения -->
        <div v-if="image.is_main" class="absolute top-2 left-2 px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded">
          Главное
        </div>
        
        <!-- Кнопки управления -->
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
          <button
            @click="setAsMain(image)"
            v-if="!image.is_main"
            class="px-3 py-1 bg-white text-gray-900 text-sm font-medium rounded hover:bg-gray-100"
          >
            Сделать главным
          </button>
          <button
            @click="deleteImage(image.id)"
            class="px-3 py-1 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700"
          >
            Удалить
          </button>
        </div>
      </div>
    </div>

    <!-- Зона загрузки -->
    <div 
      class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-red-600 transition-colors cursor-pointer"
      @click="$refs.fileInput.click()"
      @dragover.prevent
      @drop.prevent="handleDrop"
    >
      <!-- 🔧 ВАЖНО: multiple позволяет выбрать несколько файлов -->
      <input
        ref="fileInput"
        type="file"
        multiple
        accept="image/*"
        class="hidden"
        @change="handleFileSelect"
      >
      
      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      
      <p class="mt-2 text-sm text-gray-600">
        <span class="text-red-600 hover:text-red-700">Нажмите для загрузки</span> или перетащите файлы
      </p>
      <p class="mt-1 text-xs text-gray-500">PNG, JPG, WebP до 5MB (можно несколько)</p>
    </div>

    <!-- Прогресс загрузки -->
    <div v-if="uploading" class="text-center">
      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-red-600"></div>
      <p class="mt-2 text-sm text-gray-600">Загрузка...</p>
    </div>
    
    <!-- Предпросмотр выбранных файлов -->
    <div v-if="previewFiles.length > 0" class="grid grid-cols-4 gap-2">
      <div v-for="(file, index) in previewFiles" :key="index" class="relative aspect-square bg-gray-100 rounded">
        <img :src="file.preview" class="w-full h-full object-cover rounded">
        <button 
          @click="removePreview(index)"
          class="absolute -top-2 -right-2 w-6 h-6 bg-red-600 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-700"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  productId: Number
});

const images = ref([]);
const uploading = ref(false);
const previewFiles = ref([]);
const fileInput = ref(null);

const token = localStorage.getItem('api_token');

const loadImages = async () => {
  try {
    const response = await axios.get(`http://127.0.0.1:8000/api/admin/products/${props.productId}/images`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    images.value = response.data.images || [];
  } catch (error) {
    console.error('Ошибка загрузки изображений:', error);
  }
};

const handleFileSelect = (event) => {
  const files = event.target.files;
  if (files.length > 0) {
    // Создаём превью
    previewFiles.value = Array.from(files).map(file => ({
      file,
      preview: URL.createObjectURL(file)
    }));
    // Загружаем
    uploadFiles(files);
  }
};

const removePreview = (index) => {
  URL.revokeObjectURL(previewFiles.value[index].preview);
  previewFiles.value.splice(index, 1);
};

const handleDrop = (event) => {
  const files = event.dataTransfer.files;
  if (files.length > 0) {
    previewFiles.value = Array.from(files).map(file => ({
      file,
      preview: URL.createObjectURL(file)
    }));
    uploadFiles(files);
  }
};

const uploadFiles = async (files) => {
  uploading.value = true;
  
  const formData = new FormData();
  // 🔧 КЛЮЧЕВОЙ МОМЕНТ: 'images[]' с квадратными скобками
  Array.from(files).forEach(file => {
    formData.append('images[]', file);
  });

  try {
    await axios.post(
      `http://127.0.0.1:8000/api/admin/products/${props.productId}/images`, 
      formData, 
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data',
        },
      }
    );
    
    // Очищаем превью
    previewFiles.value.forEach(p => URL.revokeObjectURL(p.preview));
    previewFiles.value = [];
    
    await loadImages();
  } catch (error) {
    console.error('Ошибка загрузки:', error);
    alert('Ошибка: ' + (error.response?.data?.message || error.message));
  } finally {
    uploading.value = false;
  }
};

const setAsMain = async (image) => {
  try {
    await axios.post(
      `http://127.0.0.1:8000/api/admin/images/${image.id}/set-main`, 
      {},
      { headers: { 'Authorization': `Bearer ${token}` } }
    );
    await loadImages();
  } catch (error) {
    console.error('Ошибка:', error);
  }
};

const deleteImage = async (id) => {
  if (!confirm('Удалить это изображение?')) return;
  
  try {
    await axios.delete(
      `http://127.0.0.1:8000/api/admin/images/${id}`,
      { headers: { 'Authorization': `Bearer ${token}` } }
    );
    await loadImages();
  } catch (error) {
    console.error('Ошибка:', error);
  }
};

onMounted(() => {
  loadImages();
});
</script>