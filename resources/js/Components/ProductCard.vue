<template>
  <div class="group relative bg-white border border-gray-100 hover:shadow-lg transition duration-300">
    <!-- Image -->
    <router-link :to="`/product/${product.slug}`" class="block aspect-square overflow-hidden bg-gray-100">
      <img 
        :src="product.image ? `/storage/${product.image}` : 'https://placehold.co/400x400?text=No+Image'" 
        :alt="product.name"
        class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
      >
    </router-link>

    <!-- Info -->
    <div class="p-4">
      <router-link :to="`/product/${product.slug}`" class="block">
        <h3 class="text-lg font-medium text-gray-900 truncate">{{ product.name }}</h3>
        <p class="text-sm text-gray-500 mb-2">{{ product.material?.name || 'Серебро' }}</p>
      </router-link>
      
      <div class="flex items-center justify-between mt-2">
        <span class="text-lg font-bold text-gray-900">{{ product.price }} ₽</span>
        <button 
          @click="addToCart(product)"
          class="p-2 bg-black text-white rounded-full hover:bg-red-600 transition"
          title="Добавить в корзину"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useCartStore } from '../stores/cart';

defineProps({
  product: Object
});

const cartStore = useCartStore();

const addToCart = (product) => {
  cartStore.addToCart(product);
  // Можно добавить уведомление (toast) здесь
  alert(`Товар "${product.name}" добавлен в корзину!`);
};
</script>