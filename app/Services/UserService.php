<?php

namespace App\Services;

use App\Actions\Users\EnsureDTOHasEnoughDataToEditInfoAction;
use App\Actions\Users\GetDataToResetPasswordAction;
use App\Actions\Users\GetDataToUpdateUserInfoAction;
use App\DTOs\UserDTO;
use App\Enums\GenderEnum;
use App\Exceptions\UserException;
use App\Exceptions\UserNotFoundException;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Validation\Validator;

class UserService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
    ];
    
    public static function getUser($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            throw new UserException("Sorry 😏, user was not found.");
        }

        return $user;
    }

    public function editInfo(UserDTO $userDTO)
    {
        $userDTO = $this->setUser($userDTO);

        if (!$userDTO->user) {
            throw new UserException("Sorry 😏, user was not found.");
        }
        
        if (
            !$userDTO->currentUser->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists() &&
            !$userDTO->currentUser->is($userDTO->user)
        ) {
            throw new UserException("Sorry 😏, you are not authorized to perform this action.");
        }

        EnsureDTOHasEnoughDataToEditInfoAction::make()->execute($userDTO);
        
        $userDTO->user->update(
            GetDataToUpdateUserInfoAction::make()->execute($userDTO)
        );
        
        return $userDTO->user->refresh();
    }

    public function resetPassword(UserDTO $userDTO)
    {
        $userDTO = $this->setUser($userDTO);

        if (!$userDTO->user) {
            throw new UserException("Sorry 😏, user was not found.");
        }
        
        if (
            !$userDTO->currentUser->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists() &&
            !$userDTO->currentUser->is($userDTO->user)
        ) {
            throw new UserException("Sorry 😏, you are not authorized to perform this action.");
        }

        $userDTO->user->update(
            GetDataToResetPasswordAction::make()->execute($userDTO)
        );
        
        return $userDTO->user->refresh();
    }

    public function getAUser(string $username)
    {
        $user = User::where('username', $username)->first();

        if (is_null($user)) {
            throw new UserNotFoundException("user with $username username was not found.");
        }

        return $user;
    }

    private function setUser(UserDTO $userDTO) : UserDTO
    {
        $user = User::find($userDTO->userId);

        if (!$user) {
            $user = User::where('username', $userDTO->username)->first();
        }

        return $userDTO->withUser($user);
    }
}