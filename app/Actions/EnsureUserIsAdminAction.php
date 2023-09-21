<?php

namespace App\Actions;

use App\Actions\Action;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserIsAdminAction extends Action
{
    public function execute(BaseDTO $dto, ?string $property = "user")
    {
        EnsureUserIsOfUserTypeAction::make()->execute($dto, $property);
    }
}