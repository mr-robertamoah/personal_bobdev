<?php

namespace App\Actions\UserTypes;

use App\Actions\Action;
use App\Exceptions\UserTypeException;
use App\Models\User;

class EnsureCanCreateUserTypeAction extends Action
{
    public function execute(User $user)
    {
        if ($user->isAdmin()) {
            return;
        }

        throw new UserTypeException("Sorry 😞! You are not allowed to create a user type.");
    }
}