import { createRouter, createWebHistory } from 'vue-router';

// === ИМПОРТЫ: ПОЛЬЗОВАТЕЛЬСКИЕ СТРАНИЦЫ ===
import Home from '../views/Home.vue';
import Catalog from '../views/Catalog.vue';
import Product from '../views/Product.vue';
import Cart from '../views/Cart.vue';
import Checkout from '../views/Checkout.vue';
import Profile from '../views/Profile.vue';
import About from '../views/About.vue';
import Contacts from '../views/Contacts.vue'; 
import Services from '../views/Services.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import NotFound from '../views/NotFound.vue'; // ← Добавлено: страница 404

// === ИМПОРТЫ: АДМИН-ПАНЕЛЬ ===
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminHome from '../views/admin/Home.vue';
import AdminProducts from '../views/admin/Products.vue';
import AdminOrders from '../views/admin/Orders.vue';
import AdminCategories from '../views/admin/Categories.vue';
import AdminUsers from '../views/admin/Users.vue';
import AdminSettings from '../views/admin/Settings.vue';
import AdminContactMessages from '../views/admin/ContactMessages.vue'; 

// === МАССИВ МАРШРУТОВ ===
const routes = [
    // --- ПУБЛИЧНЫЕ МАРШРУТЫ ---
    { path: '/', name: 'Home', component: Home },
    { path: '/catalog', name: 'Catalog', component: Catalog },
    { path: '/product/:slug', name: 'Product', component: Product, props: true },
    { path: '/cart', name: 'Cart', component: Cart },
    { path: '/about', name: 'About', component: About },
    { path: '/contacts', name: 'Contacts', component: Contacts },
    { path: '/services', name: 'Services', component: Services },
    
    // --- АВТОРИЗАЦИЯ ---
    { path: '/login', name: 'Login', component: Login, meta: { guestOnly: true } },
    { path: '/register', name: 'Register', component: Register, meta: { guestOnly: true } },
    
    // --- ЗАЩИЩЁННЫЕ МАРШРУТЫ (требуется вход) ---
    { path: '/profile', name: 'Profile', component: Profile, meta: { requiresAuth: true } },
    { path: '/checkout', name: 'Checkout', component: Checkout, meta: { requiresAuth: true } },

    // --- АДМИН-ПАНЕЛЬ ---
    { 
        path: '/admin', 
        component: AdminDashboard,
        meta: { requiresAdmin: true },
        children: [
            { path: '', redirect: '/admin/dashboard' },
            { path: 'dashboard', name: 'AdminHome', component: AdminHome },
            { path: 'products', name: 'AdminProducts', component: AdminProducts },
            { path: 'orders', name: 'AdminOrders', component: AdminOrders },
            { path: 'categories', name: 'AdminCategories', component: AdminCategories },
            { path: 'users', name: 'AdminUsers', component: AdminUsers },
            { path: 'settings', name: 'AdminSettings', component: AdminSettings },
            { path: 'messages', name: 'AdminContactMessages', component: AdminContactMessages }, 
        ]
    },

    // --- 404: Страница не найдена (должна быть последней) ---
    { 
        path: '/:pathMatch(.*)*', 
        name: 'NotFound', 
        component: NotFound 
    },
];

// === СОЗДАНИЕ РОУТЕРА ===
const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: (to, from, savedPosition) => {
        // Если есть якорь (#services) — плавная прокрутка к нему
        if (to.hash) {
            return {
                el: to.hash,
                behavior: 'smooth',
            };
        }
        // Иначе — прокрутка вверх
        return savedPosition || { top: 0 };
    },
});

// === ГЛОБАЛЬНЫЙ GUARD (ЗАЩИТА МАРШРУТОВ) ===
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('api_token');
   
    let user = null;
    const userStr = localStorage.getItem('user');
    if (userStr) {
        try {
            user = JSON.parse(userStr);
        } catch (e) {
            console.warn(' Повреждённые данные пользователя в localStorage, очистка...');
            localStorage.removeItem('user');
            localStorage.removeItem('api_token');
        }
    }

    const isAuthenticated = !!token;
    const isAdmin = user?.role === 'admin';

    // 1. Защита админ-панели
    if (to.meta.requiresAdmin) {
        // Если нет токена ИЛИ пользователь не админ -> отправляем на логин
        if (!isAuthenticated || !isAdmin) {
            return next({ name: 'Login', query: { redirect: to.fullPath } });
        }
        return next();
    }

    // 2. Страница входа в админку 
    if (to.meta.adminGuest && isAuthenticated && isAdmin) {
        return next({ name: 'AdminHome' });
    }

    // 3. Защита личных маршрутов (профиль, оформление заказа)
    if (to.meta.requiresAuth && !isAuthenticated) {
        return next({ name: 'Login', query: { redirect: to.fullPath } });
    } 

    // 4. Страницы логина/регистрацииЫ
    if (to.meta.guestOnly && isAuthenticated) {
        // Если уже авторизован и пытается зайти на /login или /register:
        // Админа кидаем в админку, обычного юзера - на главную
        return next({ name: isAdmin ? 'AdminHome' : 'Home' });
    }

    // Разрешаем доступ
    return next();
}); 
export default router;