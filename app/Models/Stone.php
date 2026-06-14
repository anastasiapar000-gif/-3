<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Stone extends Model
{
    protected $fillable = ['name', 'color', 'description', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stone) {
            if (empty($stone->slug)) {
                $baseSlug = Str::slug($stone->name);
                $slug = $baseSlug;
                $counter = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $stone->slug = $slug;
            }
        });
    }
}