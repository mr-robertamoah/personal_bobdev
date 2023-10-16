<?php

namespace App\Traits;

trait HasIsLikeTrait
{
    public function scopeWhereIsLike($query, string $like)
    {
        return $query->where(function ($q) use ($like) {
            $q->where("name", "LIKE", "%{$like}%");
        })->orWhere(function ($q) use ($like) {
            $q->where("description", "LIKE", "%{$like}%");
        });
    }
}