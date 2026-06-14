import { ref, computed } from 'vue';
import axios from 'axios';

// Глобальное состояние: общий массив товаров корзины для всех компонентов
const items = ref([]);
// Глобальный флаг загрузки для отображения спиннеров
const loading = ref(false);
// Базовый URL API для работы с корзиной
const API_BASE = 'http://127.0.0.1:8000/api/cart';

// Флаг: загружена ли корзина с сервера (чтобы не делать повторные запросы)
let isInitialized = false;

// Экспортируемая функция-компоузабл для использования в компонентах
export function useCart() {
    
    // Вычисляемое свойство: общее количество единиц товаров в корзине
    const totalItems = computed(() => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    // Вычисляемое свойство: итоговая сумма всех товаров (цена * количество)
    const totalPrice = computed(() => {
        return items.value.reduce((sum, item) => sum + ((item.product?.price || 0) * item.quantity), 0);
    });

    // Метод загрузки корзины с сервера
    const loadCart = async () => {
        // Если уже загружали — выходим, чтобы не делать лишний запрос
        if (isInitialized) return;
        isInitialized = true;
        
        loading.value = true;
        try {
            // Получаем токен из хранилища
            const token = localStorage.getItem('api_token');
            // Если нет токена — пользователь не авторизован, корзина пуста
            if (!token) {
                items.value = [];
                return;
            }
            // Запрос к API с заголовком авторизации
            const { data } = await axios.get(API_BASE, {
                headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
            });
            // Обновляем глобальный массив товаров
            items.value = data || [];
        } catch (e) {
            console.error('Load cart error:', e);
            items.value = [];
        } finally {
            loading.value = false;
        }
    };

    // Метод добавления товара в корзину
    const addToCart = async (product, quantity = 1, size = null) => {
        try {
            const token = localStorage.getItem('api_token');
            // POST-запрос на добавление товара
            const { data } = await axios.post(`${API_BASE}/add`, {
                product_id: product.id,
                quantity,
                size: size ? String(size) : null
            }, {
                headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
            });
            
            // Обновляем глобальный массив корзины ответом от сервера
            items.value = data.cart || [];
            
            return { success: true, message: data.message };
        } catch (e) {
            console.error('Add to cart error:', e);
            return { success: false, message: e.response?.data?.message || 'Ошибка добавления' };
        }
    };

    // Метод удаления товара из корзины по ID позиции
    const removeFromCart = async (cartItemId) => {
        try {
            const token = localStorage.getItem('api_token');
            // DELETE-запрос на удаление позиции
            const { data } = await axios.delete(`${API_BASE}/remove/${cartItemId}`, {
                headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
            });
            // Обновляем глобальный массив
            items.value = data.cart || [];
            return { success: true };
        } catch (e) {
            console.error('Remove from cart error:', e);
            return { success: false };
        }
    };

    // Метод обновления количества товара в позиции
    const updateQuantity = async (cartItemId, quantity) => {
        try {
            const token = localStorage.getItem('api_token');
            // PUT-запрос на обновление количества
            const { data } = await axios.put(`${API_BASE}/update/${cartItemId}`, { quantity }, {
                headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
            });
            // Обновляем глобальный массив
            items.value = data.cart || [];
            return { success: true };
        } catch (e) {
            console.error('Update quantity error:', e);
            return { success: false };
        }
    };

    // Метод полной очистки корзины
    const clearCart = async () => {
        try {
            const token = localStorage.getItem('api_token');
            // DELETE-запрос на очистку
            await axios.delete(`${API_BASE}/clear`, {
                headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' }
            });
            // Очищаем локальный массив
            items.value = [];
            return { success: true };
        } catch (e) {
            console.error('Clear cart error:', e);
            return { success: false };
        }
    };

    // Автозагрузка корзины при первом вызове useCart() в приложении
    if (!isInitialized) {
        loadCart();
    }

    // Возвращаем реактивные данные и методы для использования в компонентах
    return {
        items,          // Ссылка на глобальный массив товаров
        loading,        // Флаг загрузки
        totalItems,     // Вычисляемое: общее количество
        totalPrice,     // Вычисляемое: итоговая сумма
        loadCart,       // Метод загрузки
        addToCart,      // Метод добавления
        removeFromCart, // Метод удаления
        updateQuantity, // Метод обновления количества
        clearCart       // Метод очистки
    };
}