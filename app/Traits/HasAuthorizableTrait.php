<?php

namespace App\Traits;
use App\Models\Authorization;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait HasAuthorizableTrait
{
    use HasIsLikeTrait;

    public function authorizations()
    {
        return $this->morphMany(Authorization::class, "authorizable");
    }

    public function users()
    {
        return $this->morphToMany(User::class, 'authorizable', 'authorizations');
    }
    
    public function scopeWhereName($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->where("name", $name);
        });
    }
    
    public function scopeWhereNameIsLike($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->where("name", "LIKE", "%{$name}%");
        });
    }
    
    public function scopeWhereIsOwnedBy($query, User $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->where("user_id", $model->id);
        });
    }
    
    public function scopeWhereNames($query, string $names)
    {
        return $query->where(function ($q) use ($names) {
            $q->whereIn("name", $names);
        });
    }
}