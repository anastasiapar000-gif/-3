<?php

use Illuminate\Support\Facades\Route;

// Все маршруты обрабатывает Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');