<?php

namespace App\Traits;

use App\Models\User;

trait CreatedByUserTrait
{
    public function scopeOrWhereCreatedBy(
        $query, 
        User $user,
        string $field = "user_id"
    ) {
        return $query->orWhere(function ($q) use ($user, $field) {
            $q->where($field, $user->id);
        });
    }
}