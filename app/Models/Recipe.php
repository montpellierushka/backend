<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'ingredients',
        'instructions',
        'cooking_time',
        'country',
        'image_url',
        'latitude',
        'longitude',
        'user_id'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
        'cooking_time' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class)->withPivot('order');
    }
} 