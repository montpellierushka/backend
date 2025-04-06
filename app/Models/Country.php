<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'flag',
        'description'
    ];

    /**
     * Связь с рецептами
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Связь с маршрутами
     */
    public function routes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class)
            ->withPivot('order')
            ->orderBy('country_route.order');
    }
} 