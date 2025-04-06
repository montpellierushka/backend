<?php

namespace App\Traits;

use App\Models\Like;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLikes
{
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function isLikedBy($user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function like($user)
    {
        if ($this->isLikedBy($user)) {
            return false;
        }

        return $this->likes()->create([
            'user_id' => $user->id
        ]);
    }

    public function unlike($user)
    {
        if (!$this->isLikedBy($user)) {
            return false;
        }

        return $this->likes()
            ->where('user_id', $user->id)
            ->delete();
    }
} 