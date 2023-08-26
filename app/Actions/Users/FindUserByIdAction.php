<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\Exceptions\UserException;
use App\Models\User;


class FindUserByIdAction extends Action
{
    public function execute(
        string|int $userId,
        bool $throwException = true,
        bool $showFakeId = false
    ): ?User
    {
        $user = User::find($userId);

        if ($throwException && !$user && $showFakeId) {
            throw new UserException("Sorry! User with id {$userId} was not found.");
        }

        if ($throwException && !$user) {
            throw new UserException("Sorry! User was not found.");
        }

        return $user;
    }
}