<?php

namespace App\Actions;

class GetUserDataForUserId extends Action
{
    public function execute(array | string $userData) : array
    {
        if (is_string($userData))
        {
            return [$userData, null, null];
        }

        if (count($userData) == 0)
        {
            $userData = [null, null, null];
        }

        if (count($userData) < 2)
        {
            $userData[] = null;
        }

        if (count($userData) < 3)
        {
            $userData[] = null;
        }

        return $userData;
    }
}