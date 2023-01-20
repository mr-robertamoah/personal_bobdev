<?php

namespace App\Actions\UserTypes;

use App\Actions\Action;
use App\Actions\Users\EnsureUserIsAnAdultAction;
use App\Enums\UserTypeEnum;
use App\Exceptions\UserTypeException;
use App\Models\User;

class EnsureCanBecomeUserTypeAction extends Action
{
    public function execute(User $user, string $userTypeName)
    {
        if ($user->isUserType($userTypeName)) {
            $userTypeName = strtolower($userTypeName);
            
            throw new UserTypeException("Sorry! User is already of type with name {$userTypeName}.");
        }

        if ($userTypeName == UserTypeEnum::facilitator->value) {
            EnsureUserIsAnAdultAction::make()->execute($user);
        }
    }
}