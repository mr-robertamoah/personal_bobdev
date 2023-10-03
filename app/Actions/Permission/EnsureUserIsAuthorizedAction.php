<?php

namespace App\Actions\Permission;

use App\Actions\Action;
use App\Actions\EnsureUserIsOfUserTypeAction;
use App\Enums\PermissionEnum;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureUserIsAuthorizedAction extends Action
{
    public function execute(
        BaseDTO $dto,
        ?string $property = "user",
        ?string $action = "create",
    ) {
        if ($dto->$property->isAuthorizedFor(
            name: PermissionEnum::CREATEPERMISSIONS->value
        )) return;

        EnsureUserIsOfUserTypeAction::make()->execute(
            $dto, $property, $action == "update" ? "admin" : "superadmin"
        );
    }
}