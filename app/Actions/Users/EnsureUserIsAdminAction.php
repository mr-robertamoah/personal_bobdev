<?php

namespace App\Actions\User;

use App\Actions\Action;
use App\Exceptions\UserException;
use App\Models\User;

class EnsureUserIsAdminAction extends Action
{
    public function execute(User $user)
    {
        if ($user->isAdmin()) {
            return;
        }

        throw new UserException("Sorry! User with name {$user->name} has to be an administrator for this platform in order to perform this action.");
    }
}