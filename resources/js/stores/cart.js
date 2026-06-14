// Pinia-хранилище для управления состоянием корзины покупок
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

// Создаём хранилище 'cart' с помощью defineStore
export const useCartStore = defineStore('cart', () => {
    
    // === STATE: Реактивные переменные состояния ===
    
    // Массив товаров в корзине (изначально пустой)
    const items = ref([]);
    
    // Флаг загрузки: true во время выполнения асинхронных операций
    const loading = ref(false);
    
    // Сообщение об ошибке: содержит текст последней ошибки или null
    const error = ref(null);

    // === GETTERS: Вычисляемые свойства ===
    
    // Вычисляет итоговую сумму корзины: сумма (цена * количество) для всех товаров
    const total = computed(() => {
        return items.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    });

    // Вычисляет общее количество единиц товаров в корзине
    const count = computed(() => {
        return items.value.reduce((sum, item) => sum + item.quantity, 0);
    });

    // === ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ===

    // Нормализует объект товара корзины: гарантирует наличие всех обязательных полей
    // Это нужно для единообразия данных, полученных от API или из локального хранилища
    function normalizeCartItem(item) {
        return {
            id: item.id,
            product_id: item.product_id,
            name: item.name || item.product?.name || '',
            price: item.price || item.product?.price || 0,
            image: item.image || item.product?.image || null,
            // Гарантируем, что поле size всегда присутствует (даже если равно null)
            size: item.size !== undefined ? item.size : null,
            quantity: item.quantity || 1,
            // Уникальный ключ для идентификации позиции: продукт + размер
            cartKey: item.cartKey || (item.size ? `${item.product_id}-size-${item.size}` : `${item.product_id}-no-size`),
            // Сохраняем полный объект product для проверки остатков и других данных
            product: item.product || null,
        };
    }

    // Форматирует числовое значение цены в строку с разделителями тысяч и символом рубля
    function formatPrice(value) {
        return Number(value).toLocaleString('ru-RU') + ' ₽';
    }

    // Возвращает читаемую метку размера для отображения в интерфейсе
    // Если размер не указан — возвращает null (чтобы не показывать пустую строку)
    function getItemSizeLabel(item) {
        // Проверяем, что size существует и не является пустой строкой
        if (item.size != null && String(item.size).trim() !== '') {
            return `Размер: ${item.size}`;
        }
        return null;
    }

    // === API-МЕТОДЫ: Асинхронные операции с сервером ===

    // Загружает актуальное состояние корзины с сервера
    async function fetchCart() {
        loading.value = true;
        error.value = null;
        try {
            const token = localStorage.getItem('api_token');
            const { data } = await axios.get('/api/cart', {
                headers: { Authorization: `Bearer ${token}` }
            });
            
            // Нормализуем каждый элемент ответа, чтобы гарантировать структуру данных
            items.value = (data || []).map(normalizeCartItem);
        } catch (e) {
            // Сохраняем сообщение об ошибке для отображения пользователю
            error.value = e.response?.data?.message || 'Ошибка загрузки корзины';
            console.error('Ошибка загрузки корзины:', e);
        } finally {
            // Сбрасываем флаг загрузки независимо от результата
            loading.value = false;
        }
    }

    // Добавляет товар в корзину через API
    // Параметры: product (объект товара), quantity (количество), size (опционально, для колец)
    async function addToCart(product, quantity = 1, size = null) {
        loading.value = true;
        error.value = null;
        try {
            const token = localStorage.getItem('api_token');
            const { data } = await axios.post('/api/cart/add', {
                product_id: product.id,
                quantity,
                size // Передаём размер, если товар имеет варианты
            }, {
                headers: { Authorization: `Bearer ${token}` }
            });
            
            // Обновляем локальное состояние корзины нормализованными данными от сервера
            items.value = (data.cart || []).map(normalizeCartItem);
            return { success: true, message: data.message };
        } catch (e) {
            // Сохраняем ошибку и возвращаем объект с флагом неуспеха
            error.value = e.response?.data?.message || 'Ошибка добавления';
            console.error('Ошибка добавления в корзину:', e);
            return { success: false, message: error.value };
        } finally {
            loading.value = false;
        }
    }

    // Удаляет товар из корзины по ID позиции (cartItemId)
    async function removeFromCart(cartItemId) {
        loading.value = true;
        error.value = null;
        try {
            const token = localStorage.getItem('api_token');
            const { data } = await axios.delete(`/api/cart/remove/${cartItemId}`, {
                headers: { Authorization: `Bearer ${token}` }
            });
            // Обновляем локальное состояние после успешного удаления
            items.value = (data.cart || []).map(normalizeCartItem);
            return { success: true };
        } catch (e) {
            error.value = e.response?.data?.message || 'Ошибка удаления';
            return { success: false };
        } finally {
            loading.value = false;
        }
    }

    // Полностью очищает корзину на сервере и локально
    async function clearCart() {
        loading.value = true;
        error.value = null;
        try {
            const token = localStorage.getItem('api_token');
            const { data } = await axios.delete('/api/cart/clear', {
                headers: { Authorization: `Bearer ${token}` }
            });
            // Очищаем локальный массив товаров
            items.value = [];
            return { success: true };
        } catch (e) {
            error.value = e.response?.data?.message || 'Ошибка очистки';
            return { success: false };
        } finally {
            loading.value = false;
        }
    }

    // === ЛОКАЛЬНЫЕ МЕТОДЫ: Работа без авторизации (оффлайн-режим) ===
    
    // Добавляет товар в локальную корзину (без отправки на сервер)
    // Используется для гостей, которые ещё не авторизовались
    function addLocalItem(cartItem) {
        const normalized = normalizeCartItem(cartItem);
        // Ищем существующую позицию с таким же cartKey (продукт + размер)
        const existingIndex = items.value.findIndex(i => i.cartKey === normalized.cartKey);
        
        if (existingIndex > -1) {
            // Если товар уже есть — увеличиваем количество
            items.value[existingIndex].quantity += normalized.quantity;
        } else {
            // Если товара нет — добавляем новую позицию
            items.value.push(normalized);
        }
        // Сохраняем обновлённую корзину в localStorage для восстановления после перезагрузки
        localStorage.setItem('zima_cart_local', JSON.stringify(items.value));
    }
    
    // Загружает сохранённую локальную корзину из localStorage
    // Вызывается при инициализации приложения для неавторизованных пользователей
    function loadLocalCart() {
        try {
            const saved = localStorage.getItem('zima_cart_local');
            if (saved) {
                const parsed = JSON.parse(saved);
                // Нормализуем каждый элемент при загрузке
                items.value = parsed.map(normalizeCartItem);
            }
        } catch (e) {
            // Логируем предупреждение, но не прерываем работу приложения
            console.warn('Ошибка загрузки локальной корзины:', e);
        }
    }

    // Возвращаем публичный API хранилища для использования в компонентах
    return {
        // Состояние
        items,      // Массив товаров в корзине
        loading,    // Флаг выполнения асинхронной операции
        error,      // Текст последней ошибки
        
        // Вычисляемые свойства
        total,      // Итоговая сумма корзины
        count,      // Общее количество товаров
        
        // Асинхронные методы работы с сервером
        fetchCart,      // Загрузка корзины с сервера
        addToCart,      // Добавление товара
        removeFromCart, // Удаление товара
        clearCart,      // Очистка корзины
        
        // Вспомогательные методы
        formatPrice,       // Форматирование цены
        getItemSizeLabel,  // Получение метки размера для отображения
        
        // Локальные методы (для неавторизованных пользователей)
        addLocalItem,      // Добавление в локальную корзину
        loadLocalCart,     // Загрузка локальной корзины
        
        // Экспортируем нормализацию для отладки и тестов
        normalizeCartItem,
    };
});