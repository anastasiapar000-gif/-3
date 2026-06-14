import axios from 'axios';
window.axios = axios;

// Базовый URL вашего Laravel API
axios.defaults.baseURL = 'http://localhost:8000/api'; 

// Автоматическая отправка CSRF токена (для сессионной аутентификации Breeze/Sanctum Web)
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Для Sanctum Token Auth :
// const apiToken = localStorage.getItem('api_token');
// if (apiToken) {
//     window.axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;
// }