<?php

namespace App\Actions\UserTypes;

use App\Actions\Action;
use App\Models\User;
use App\Models\UserType;

class CanAttachOrDetachUserTypeAction extends Action
{
    public function execute(User $currentUser, User $user): bool
    {
        if ($currentUser->isAdmin() || $currentUser->is($user)) {
            return true;
        }

        return false;
    }
}