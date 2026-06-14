<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    
    public function index()
    {
        try {
            // Загружаем материалы, выбирая только необходимые поля
            $materials = Material::select('id', 'name', 'slug', 'description')
                ->orderBy('name')
                ->get();
                
            return response()->json($materials);
            
        } catch (\Exception $e) {
            // Логируем ошибку и возвращаем ответ с кодом 500
            \Log::error('Materials index error: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка загрузки материалов'], 500);
        }
    }
}