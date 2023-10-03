<?php

namespace App\Actions;

use App\Actions\Action;
use App\Enums\PermissionEnum;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserIsAdminAction extends Action
{
    public function execute(BaseDTO $dto, ?string $property = "user")
    {
        if ($dto->$property->isAuthorizedFor(
            name: PermissionEnum::CREATEPERMISSIONS->value
        )) return;

        EnsureUserIsOfUserTypeAction::make()->execute($dto, $property);
    }
}