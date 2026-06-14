<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Material extends Model
{
    protected $fillable = ['name', 'description', 'slug'];

    protected static function boot()
    {
        parent::boot();

        // Автоматическая генерация slug при создании
        static::creating(function ($material) {
            if (empty($material->slug)) {
                $baseSlug = Str::slug($material->name);
                $slug = $baseSlug;
                $counter = 1;

                // Проверка на уникальность, если такой slug уже есть
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $material->slug = $slug;
            }
        });
    }
}