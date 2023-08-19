<?php

namespace App\Actions;

class GetUserDataForUserId extends Action
{
    public function execute(array | string $userData) : array
    {
        if (is_string($userData))
        {
            return [$userData, null];
        }

        if (count($userData) == 0)
        {
            $userData = [null, null];
        }

        if (count($userData) == 1)
        {
            $userData[] = null;
        }

        return $userData;
    }
}