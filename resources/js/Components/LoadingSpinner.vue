<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm transition-opacity duration-300">
    <div class="flex flex-col items-center">
    
      <div 
        :class="[
          'rounded-full border-4 border-t-red-600 border-red-100 animate-spin',
          sizeClasses[size]
        ]"
      ></div>
      
      <!-- Текст загрузки -->
      <p v-if="text" class="mt-4 text-gray-600 font-medium text-sm tracking-wide">
        {{ text }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: true
  },
  size: {
    type: String,
    default: 'lg', // 'sm', 'md', 'lg'
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  text: {
    type: String,
    default: 'Загрузка...'
  }
});

// Маппинг размеров на классы Tailwind
const sizeClasses = {
  sm: 'w-6 h-6 border-2',
  md: 'w-10 h-10 border-4',
  lg: 'w-16 h-16 border-4'
};
</script>