<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\Exceptions\UserException;
use App\Models\User;

class EnsureUserIsAnAdultAction extends Action
{
    public function execute(User $user)
    {
        if (is_null($user->age)) {
            throw new UserException("Sorry! User, with name {$user->name}, has not yet specified date of birth.");
        }

        if ($user->isAdult()) {
            return;
        }

        throw new UserException("Sorry! User, with name {$user->name}, is not yet an adult.");
    }
}