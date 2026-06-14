<template>
  <div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Настройки сайта</h1>

    <div class="bg-white rounded-xl shadow p-6 space-y-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Название магазина</label>
        <input v-model="settings.name" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email поддержки</label>
        <input v-model="settings.email" type="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Телефон</label>
        <input v-model="settings.phone" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Текст в футере</label>
        <textarea v-model="settings.footer_text" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
      </div>
      <button @click="save" class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
        Сохранить настройки
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';



const save = () => {
  // Здесь можно добавить отправку на сервер
  alert('Настройки сохранены!');
};
</script>