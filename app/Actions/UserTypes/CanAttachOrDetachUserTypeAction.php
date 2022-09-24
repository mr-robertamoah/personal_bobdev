<?php

namespace App\Actions\UserTypes;

use App\Models\User;
use App\Models\UserType;

class CanAttachOrDetachUserTypeAction
{

    public function execute(User $currentUser, User $user): bool
    {
        if ($currentUser->isAdmin() || $currentUser->is($user)) {
            return true;
        }

        return false;
    }
}