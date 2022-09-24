<?php

namespace App\Actions\Users;

use App\Exceptions\UserException;
use App\Models\User;


class FindUserByIdAction
{
    public function execute(string|int $userId, $throwException = true): User
    {
        $user = User::find($userId);

        if ($throwException && !$user) {
            throw new UserException("Sorry! user was not found.");
        }

        return $user;
    }
}