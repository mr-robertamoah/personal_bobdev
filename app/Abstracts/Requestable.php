<?php

namespace App\Abstracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

abstract class Requestable extends Model
{
    abstract public function isOfficial(User $user): bool;

    // requestables must be considered admins when they are senders of requests
    public function isAdmin(): bool
    {
        return true;
    }
}