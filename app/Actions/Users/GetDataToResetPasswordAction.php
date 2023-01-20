<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\DTOs\UserDTO;
use App\Exceptions\UserException;
use App\Services\UserService;

class GetDataToResetPasswordAction extends Action
{
    public function execute(UserDTO $userDTO): array
    {
        $data = [];

        EnsureDTOHasEnoughDataToResetPasswordAction::make()->make($userDTO);
        
        if (
            !$userDTO->currentUser->userTypes()->whereIn('name', UserService::AUTHORIZEDUSERTYPES)->exists() &&
            !password_verify($userDTO->currentPassword, $userDTO->user->password)
        ) {
            throw new UserException("Sorry 😏, the current password is wrong.");
        }
        
        if (
            strlen($userDTO->password) < 6 
        ) {
            throw new UserException("Sorry 😏, the password and the confirmation passwords do not match.");
        }

        if (
            $userDTO->password !== $userDTO->passwordConfirmation
        ) {
            throw new UserException("Sorry 😏, the password and the confirmation passwords do not match.");
        }

        $data['password'] = bcrypt($userDTO->password);
        
        return $data;
    }
}