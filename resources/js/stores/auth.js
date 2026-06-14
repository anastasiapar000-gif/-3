// resources/js/stores/auth.js
import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import axios from 'axios';

// Создаём Pinia-хранилище 'auth' для управления состоянием аутентификации
export const useAuthStore = defineStore('auth', () => {
    
    // === STATE: Реактивные переменные состояния ===
    
    // Вспомогательная функция: безопасное чтение данных пользователя из localStorage при инициализации
    const initUser = () => {
        try {
            const stored = localStorage.getItem('user');
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            // Логируем ошибку парсинга и очищаем повреждённые данные
            console.error('Ошибка чтения данных пользователя из хранилища:', e);
            localStorage.removeItem('user');
            return null;
        }
    };

    // Реактивная переменная: данные текущего пользователя (изначально берём из localStorage)
    const user = ref(initUser());
    
    // Реактивная переменная: токен авторизации (изначально берём из localStorage)
    const token = ref(localStorage.getItem('api_token') || null);

    // === GETTERS: Вычисляемые свойства ===
    
    // Возвращает true, если есть токен (пользователь авторизован)
    const isAuthenticated = computed(() => !!token.value);
    
    // Возвращает true, если роль пользователя — 'admin'
    const isAdmin = computed(() => user.value?.role === 'admin');
    
    // Возвращает отображаемое имя: full_name → name → часть email до @ → 'Пользователь'
    const userName = computed(() => 
        user.value?.full_name || 
        user.value?.name || 
        user.value?.email?.split('@')[0] || 
        'Пользователь'
    );

    // ===  Методы для изменения состояния ===
    
    // Устанавливает или удаляет заголовок Authorization в глобальных настройках axios
    function setAuthHeader() {
        if (token.value) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        } else {
            delete axios.defaults.headers.common['Authorization'];
        }
    }

    // Синхронизирует состояние (token, user) с localStorage и обновляет заголовки axios
    function persistState() {
        if (token.value) {
            localStorage.setItem('api_token', token.value);
            localStorage.setItem('user', JSON.stringify(user.value));
        } else {
            localStorage.removeItem('api_token');
            localStorage.removeItem('user');
        }
        setAuthHeader();
    }

    // Слушатели: автоматически вызывают persistState() при изменении token или user
    // deep: true для user нужен, чтобы отслеживать изменения внутри объекта
    watch(token, () => persistState());
    watch(user, () => persistState(), { deep: true });

    // Метод входа: отправляет учетные данные на сервер, сохраняет токен и данные пользователя
    async function login(email, password) {
        const res = await axios.post('/login', { email, password });
        
        // Обновляем реактивные переменные — watch автоматически сохранит их в localStorage
        token.value = res.data.token;
        user.value = res.data.user;
        
        return res.data;
    }

    // Метод регистрации: создаёт нового пользователя и сразу авторизует его
    async function register(full_name, email, password, password_confirmation) {
        const res = await axios.post('/register', { 
            full_name,  
            email, 
            password, 
            password_confirmation 
        });
        
        // Сохраняем полученные токен и данные пользователя
        token.value = res.data.token;
        user.value = res.data.user;
        
        return res.data;
    }

    // Метод выхода: отправляет запрос на сервер для отзыва токена, затем очищает локальное состояние
    async function logout() {
        try {
            if (token.value) {
                await axios.post('/logout');
            }
        } catch (e) {
            // Логируем ошибку, но продолжаем очистку локально (пользователь всё равно вышел)
            console.error('Ошибка при выходе из системы:', e);
        } finally {
            // Очищаем реактивные переменные — watch автоматически очистит localStorage
            token.value = null;
            user.value = null;
        }
    }

    // Метод проверки авторизации: запрашивает данные текущего пользователя с сервера
    // Используется при загрузке приложения для валидации токена
    async function checkAuth() {
        if (!token.value) return false;
        try {
            const res = await axios.get('/user');
            user.value = res.data;
            return true;
        } catch {
            // Если токен невалиден — выполняем полный выход
            await logout();
            return false;
        }
    }

    // Инициализация: устанавливаем заголовок авторизации при первом запуске хранилища
    setAuthHeader();

    // Возвращаем публичный API хранилища для использования в компонентах
    return { 
        user,           // Данные пользователя (реактивные)
        token,          // Токен авторизации (реактивный)
        isAuthenticated, // Вычисляемое: авторизован ли пользователь
        isAdmin,         // Вычисляемое: является ли пользователь администратором
        userName,        // Вычисляемое: отображаемое имя пользователя
        login,           // Метод: вход в систему
        register,        // Метод: регистрация нового пользователя
        logout,          // Метод: выход из системы
        checkAuth,       // Метод: проверка валидности токена
    };
});