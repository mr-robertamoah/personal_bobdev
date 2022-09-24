<?php

namespace App\Services\UserTypeService;

use App\Actions\Users\FindUserByIdAction;
use App\Actions\UserTypes\RemoveUserTypeAction;
use App\Actions\UserTypes\BecomeUserTypeAction;
use App\DTOs\UserTypeDTO;
use App\Exceptions\UserTypeException;
use App\Models\User;
use App\Models\UserType;

class UserTypeService {

    public function createUserType(UserTypeDTO $userTypeDTO)
    {
        $this->ensureCanCreateUserType($userTypeDTO);

        $this->create($userTypeDTO);
    }

    private function create(UserTypeDTO $userTypeDTO): UserType
    {
        $userType = $userTypeDTO->user->userTypes()->create(
            $userTypeDTO->getData(filled: true)
        );

        if (is_null($userType)) {
            throw new UserTypeException("Sorry 😞! The creation of the user type with name " . $userTypeDTO->name . " failed.");
        }

        return $userType;
    }

    private function ensureCanCreateUserType(UserTypeDTO $userTypeDTO)
    {
        if ($userTypeDTO->user->isAdmin()) {
            return;
        }

        throw new UserTypeException("Sorry 😞! You are not allowed to create a user type.");
    }

    public function becomeUserType(UserTypeDTO $userTypeDTO)
    {
        $userTypeDTO = $this->updateUserTypeDTO($userTypeDTO);

        return (new BecomeUserTypeAction)->execute($userTypeDTO);
    }

    public function removeUserType(UserTypeDTO $userTypeDTO)
    {
        $userTypeDTO = $this->updateUserTypeDTO($userTypeDTO);

        return (new RemoveUserTypeAction)->execute($userTypeDTO);
    }

    private function updateUserTypeDTO(UserTypeDTO $userTypeDTO)
    {
        return $userTypeDTO = $userTypeDTO->withUser(
            (new FindUserByIdAction)->execute($userTypeDTO->userId)
        )->withAttachedUser(
            (new FindUserByIdAction)->execute($userTypeDTO->attachedUserId)
        );
    }
}