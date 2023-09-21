<?php

namespace App\Actions;

use App\Actions\Action;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserIsSuperAdminAction extends Action
{
    public function execute(BaseDTO $dto, ?string $property = "user")
    {
        EnsureUserIsOfUserTypeAction::make()->execute(
            $dto, $property, 'superadmin'
        );
    }
}