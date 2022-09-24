<?php

namespace App\Traits;

use App\Models\Profile;

trait HasProfileTrait
{
    public function profile()
    {
        return $this->morphOne(Profile::class, 'profileable');
    }

    public function hasProfile() : bool
    {
        return $this->has('profile');
    }

    public function doesntHaveProfile() : bool
    {
        return ! $this->hasProfile();
    }
}