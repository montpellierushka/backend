<?php

namespace App\Models;

use App\Traits\HasLikes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Route extends Model
{
    use HasFactory;
    use HasLikes;

    protected $fillable = [
        'name',
        'description',
        'image',
        'user_id',
    ];

    /**
     * Связь со странами
     */
    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class)
            ->withPivot('order')
            ->orderBy('country_route.order');
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
        return $this->belongsToMany(User::class, 'favorite_routes')
            ->withTimestamps();
    }
} 