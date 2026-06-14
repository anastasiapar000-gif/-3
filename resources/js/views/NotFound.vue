<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="text-center max-w-md">
      <!-- Иконка 404 -->
      <div class="mb-6">
        <svg class="w-24 h-24 mx-auto text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      
      <!-- Заголовок -->
      <h1 class="text-6xl font-bold text-gray-900 mb-2">404</h1>
      <p class="text-xl text-gray-600 mb-6">Страница не найдена</p>
      
      <!-- Описание -->
      <p class="text-gray-500 mb-8">
        Возможно, страница была удалена, перемещена или вы ввели неверный адрес.
      </p>
      
      <!-- Кнопки -->
      <div class="space-y-3">
        <router-link 
          to="/" 
          class="block w-full py-3 px-6 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition"
        >
          Вернуться на главную
        </router-link>
        
        <button 
          @click="$router.go(-1)" 
          class="block w-full py-3 px-6 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:border-gray-400 transition"
        >
          Назад
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
// Страница 404 — не требует логики
</script>