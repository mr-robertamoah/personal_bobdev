<?php

namespace App\Actions\UserTypes;

use App\DTOs\UserTypeDTO;
use App\Exceptions\UserTypeException;
use App\Models\User;
use App\Models\UserType;

class BecomeUserTypeAction
{

    public function execute(UserTypeDTO $userTypeDTO)
    {
        if (! (new CanAttachOrDetachUserTypeAction)->execute($userTypeDTO->user, $userTypeDTO->attachedUser)) {
            throw new UserTypeException("Sorry! You are not allowed to perform this action regarding the user type with name {$userTypeDTO->name}");
        }

        $userTypeName = strtoupper($userTypeDTO->name);

        if (
            $userTypeName === UserType::ADMIN &&
            ! $userTypeDTO->user->isSuperAdmin()
        ) {
            throw new UserTypeException("Sorry! Only a Super Administrator is allowed to perform this action.");
        }

        if (! in_array($userTypeName, UserType::TYPES)) {
            throw new UserTypeException("Sorry! There is no user type with the name {$userTypeDTO->name}.");
        }

        if ($userTypeDTO->attachedUser->isUserType($userTypeName)) {
            throw new UserTypeException("Sorry! User is already of type with name {$userTypeDTO->name}.");
        }

        $userType = UserType::withName($userTypeName);

        $userTypeDTO->attachedUser->userTypes()->attach(
            $userType->id
        );

        return $userType;
    }
}