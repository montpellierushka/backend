<?php 

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'telegram_id',
        'telegram_username',
        'telegram_first_name',
        'telegram_last_name',
        'telegram_photo_url'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Получение рецептов пользователя
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * Получение маршрутов пользователя
     */
    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    /**
     * Получение избранных рецептов пользователя
     */
    public function favoriteRecipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'favorite_recipes')
            ->withTimestamps();
    }

    /**
     * Получение избранных маршрутов пользователя
     */
    public function favoriteRoutes(): BelongsToMany
    {
        return $this->belongsToMany(Route::class, 'favorite_routes')
            ->withTimestamps();
    }
}
