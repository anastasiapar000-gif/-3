import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        zima: {
          black: '#0a0a0a',
          dark: '#171717',
          red: '#a10000',
        }
      },
      //  Настройка шрифтов
      fontFamily: {
        sans: ['Mathison', ...defaultTheme.fontFamily.sans], // Основной шрифт для всего сайта
        serif: ['Mathison', ...defaultTheme.fontFamily.serif], // Если нужен для акцентов
      },
    },
  },
  // Плагины
  plugins: [
    forms,
  ],
}