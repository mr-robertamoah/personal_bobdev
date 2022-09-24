<?php

namespace App\Services\UserService;

use App\Exceptions\UserException;
use App\Models\User;

class UserService
{
    public static function getUser($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            throw new UserException("Sorry 😏, user was not found.");
        }

        return $user;
    }
}