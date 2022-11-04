<?php

namespace App\Services;

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

        $this->ensureDTOHasEnoughDataToEditInfo($userDTO);
        
        $data = [];

        if ($userDTO->firstName) {
            $data['first_name'] = $userDTO->firstName;
        }

        if ($userDTO->surname) {
            $data['surname'] = $userDTO->surname;
        }

        if ($userDTO->otherNames) {
            $data['other_names'] = $userDTO->otherNames;
        }

        if ($userDTO->email) {
            $data['email'] = $userDTO->email;
        }
        
        $data = $this->setGender($data, $userDTO);

        $userDTO->user->update($data);
        
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

        $data = $this->setPassword($userDTO);

        $userDTO->user->update($data);
        
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

    private function setGender(array $data, UserDTO $userDTO): array
    {
        if (!$userDTO->gender) {
            return $data;
        }

        $userDTO->gender = strtoupper($userDTO->gender);

        if (!in_array(GenderEnum::from($userDTO->gender), GenderEnum::cases())) {
            throw new UserException("Sorry 😞, male or female is required set gender");
        }

        $data['gender'] = $userDTO->gender;
        
        return $data;
    }

    private function setPassword(UserDTO $userDTO): array
    {
        $data = [];

        $this->ensureDTOHasEnoughDataToResetPassword($userDTO);
        
        if (
            !$userDTO->currentUser->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists() &&
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

    private function ensureDTOHasEnoughDataToEditInfo(UserDTO $userDTO)
    {
        if (
            $userDTO->firstName || $userDTO->surname || $userDTO->otherNames || 
            $userDTO->email || $userDTO->gender
        ) {
            return;
        }

        throw new UserException("Sorry, you do not have enough data to perform this action.");
    }

    private function ensureDTOHasEnoughDataToResetPassword(UserDTO $userDTO)
    {
        if ($userDTO->password) {
            return;
        }

        throw new UserException("Sorry, password is required to perform this action.");
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