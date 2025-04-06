<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id',
        'step_number',
        'description',
        'image'
    ];

    /**
     * Связь с рецептом
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
} 