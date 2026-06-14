<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// === API Controllers ===
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\ContactController;

// === Admin Controllers ===
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\ReportController;

// =============================================================================
// === PUBLIC ROUTES (БЕЗ авторизации) ===
// =============================================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Публичные данные каталога
Route::get('/products', [ApiProductController::class, 'index']);
Route::get('/products/{slug}', [ApiProductController::class, 'show']);
Route::get('/categories', [ApiCategoryController::class, 'index']);

// Форма обратной связи
Route::post('/contact', [ContactController::class, 'submit']);


// =============================================================================
// === PROTECTED USER ROUTES (Требуется вход пользователя) ===
// =============================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) { 
        return response()->json($request->user());
    });
    
    Route::put('/user/profile', [ProfileController::class, 'update']);
    
    // Корзина
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'removeFromCart']);
    Route::put('/cart/update/{cartItem}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    
    // Заказы
    Route::post('/checkout', [ApiOrderController::class, 'checkout']);
    Route::get('/user/orders', [ApiOrderController::class, 'userOrders'])->name('user.orders');
    Route::get('/user/orders/{id}', [ApiOrderController::class, 'showUserOrder'])->name('user.orders.show');
    Route::put('/user/orders/{id}/cancel', [ApiOrderController::class, 'cancelOrder'])->name('user.orders.cancel');
    
    // Адреса
    Route::get('/user/addresses', [AddressController::class, 'index']);
    Route::post('/user/addresses', [AddressController::class, 'store']);
    Route::put('/user/addresses/{address}/default', [AddressController::class, 'setDefault']);
    Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy']);
    
    // Выход
    Route::post('/logout', [AuthController::class, 'logout']);
});


// =============================================================================
// === ADMIN ROUTES (Требуется вход + роль admin) ===
// =============================================================================
Route::prefix('admin')
    ->middleware(['auth:sanctum', 'admin'])
    ->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/orders/recent', [DashboardController::class, 'recentOrders']);
        
        // Товары
        Route::apiResource('products', AdminProductController::class);
        Route::get('/stones', [AdminProductController::class, 'getStones']);
        Route::get('/materials', [MaterialController::class, 'index']);
        
        // Изображения
        Route::prefix('products/{product}')->group(function () {
            Route::post('/images', [ProductImageController::class, 'store']);
            Route::get('/images', [ProductImageController::class, 'index']);
            Route::put('/images/reorder', [ProductImageController::class, 'reorder']);
        });
        Route::delete('/images/{image}', [ProductImageController::class, 'destroy']);
        Route::post('/images/{image}/set-main', [ProductImageController::class, 'setMain']);
        
        // Заказы
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::delete('/orders/{order}', [AdminOrderController::class, 'destroy']);
        
        // Категории
        Route::apiResource('categories', AdminCategoryController::class);
        
        // Пользователи
        Route::get('/users', [UserController::class, 'index']);
        Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        // Сообщения
        Route::get('/contact-messages', [ContactMessageController::class, 'index']);
        Route::put('/contact-messages/{contactMessage}/read', [ContactMessageController::class, 'markAsRead']);
        Route::delete('/contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy']);
        
        // =====================================================================
        // === ОТЧЁТЫ ===
        // =====================================================================
        Route::prefix('reports')->group(function () {
            Route::get('/orders/excel', [ReportController::class, 'exportOrdersExcel'])->name('admin.reports.orders');
            
            Route::get('/products/excel', [ReportController::class, 'exportProductsExcel']);
        });
    });

Route::fallback(function () {
    return response()->json(['message' => 'Маршрут не найден'], 404);
});