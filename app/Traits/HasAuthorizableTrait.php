<?php

namespace App\Traits;
use App\Models\Authorization;

trait HasAuthorizableTrait
{
    public function authorizations()
    {
        return $this->morphMany(Authorization::class);
    }

    public function users()
    {
        return $this->morphToMany(User::class, 'authorizable', 'authorizations');
    }
}