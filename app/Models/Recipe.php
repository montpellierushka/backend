<?php

namespace App\Models;

use App\Traits\HasLikes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recipe extends Model
{
    use HasFactory;
    use HasLikes;

    protected $fillable = [
        'title',
        'description',
        'cooking_time',
        'servings',
        'country_id',
        'image',
        'user_id',
        'difficulty',
        'latitude',
        'longitude'
    ];

    protected $appends = ['image_url', 'is_favorite'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function getIsFavoriteAttribute()
    {
        // Временное решение - используем ID 1 как тестового пользователя
        $userId = 1;
        return $this->favoritedBy()->where('user_id', $userId)->exists();
    }

    /**
     * Связь с тегами
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Связь со страной
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Связь с ингредиентами
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Связь с шагами приготовления
     */
    public function steps(): HasMany
    {
        return $this->hasMany(RecipeStep::class);
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorite_recipes')
            ->withTimestamps();
    }

    /**
     * Алиас для метода favoritedBy
     */
    public function favorites(): BelongsToMany
    {
        return $this->favoritedBy();
    }
} 