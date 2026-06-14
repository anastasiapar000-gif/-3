<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Проверяю, авторизован ли пользователь
        if (!$request->user()) {
            return response()->json([
                'message' => 'Необходима авторизация'
            ], 401);
        }

        // Проверяю роль администратора
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Доступ запрещен. Требуется роль администратора.'
            ], 403);
        }

        return $next($request);
    }
}