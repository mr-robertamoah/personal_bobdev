<?php

namespace App\Actions\UserTypes;

use App\Actions\UserTypes\CanAttachOrDetachUserTypeAction;
use App\DTOs\UserTypeDTO;
use App\Exceptions\UserTypeException;
use App\Models\User;
use App\Models\UserType;

class RemoveUserTypeAction
{
    public function execute(UserTypeDTO $userTypeDTO)
    {
        if (
            ! (new CanAttachOrDetachUserTypeAction)
                ->execute($userTypeDTO->user, $userTypeDTO->attachedUser)
        ) {
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

        if (! $userTypeDTO->attachedUser->hasUserType($userTypeName)) {
            throw new UserTypeException("Sorry! User is not of {$userTypeDTO->name} type.");
        }

        $userType = UserType::withName($userTypeName);

        return $userTypeDTO->attachedUser->userTypes()->detach(
            $userType->id
        );
        
    }
}