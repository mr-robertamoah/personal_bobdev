<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\DTOs\UserDTO;
use App\Enums\GenderEnum;
use App\Exceptions\UserException;
use App\Services\UserService;
use Carbon\Carbon;

class GetDataToUpdateUserInfoAction extends Action
{
    public function execute(UserDTO $userDTO): array
    {
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

        $data = $this->setDOB($data, $userDTO);
        
        return $data;
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

    private function setDOB(array $data, UserDTO $userDTO): array
    {
        if (!$userDTO->dob) {
            return $data;
        }

        $date = Carbon::parse($userDTO->dob);

        if (is_null($date)) {
            throw new UserException("Sorry 😞, {$userDTO->dob} is not a valid date");
        }

        $data['dob'] = $date->toDateTimeString();
        
        return $data;
    }
}