<?php

namespace App\Actions;

use App\Actions\Action;
use App\Exceptions\ServiceException;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserIsOfUserTypeAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        ?string $property = "user",
        string $userType = "admin",
    ) {
        $method = "is" . ucfirst(strtolower($userType));

        if ($dto->$property?->$method()) {
            return;
        }
        
        throw new ServiceException("Sorry! For this action, {$dto->$property?->name} must be a super administrator.", 422);
    }
}