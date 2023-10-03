<?php

namespace App\Traits;

use App\Models\User;

trait HasAuthorizationTrait
{
    use CreatedByUserTrait;
    
    public function scopeWhereName($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->where("name", $name);
        });
    }
    
    public function scopeWhereNames($query, string $names)
    {
        return $query->where(function ($q) use ($names) {
            $q->whereIn("name", $names);
        });
    }

    public function scopeWherePublic($query)
    {
        return $query->where(function ($q) {
            $q->where("public", 1);
        });
    }

    public function scopeOrWherePrivate($query) {
        return $query->orWhere(function ($q) {
            $q->where("public", 0);
        });
    }

    public function scopeWhereClass($query, ?string $class = null)
    {
        return $query->where(function ($q) use ($class) {
            $q->where("class", $class);
        });
    }

    public function scopeWhereIsLike($query, string $like)
    {
        return $query->where(function ($q) use ($like) {
            $q->where("name", "LIKE", $like);
        })->orWhere(function ($q) use ($like) {
            $q->where("description", "LIKE", $like);
        });
    }
}