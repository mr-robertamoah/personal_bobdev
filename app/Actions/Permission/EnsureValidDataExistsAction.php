<?php

namespace App\Actions\Permission;
use App\Actions\Action;
use App\Models\Permission;
use MrRobertAmoah\DTO\BaseDTO;

class EnsureValidDataExistsAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        bool $nullable = false,
        string $for = "permission"
    ) {
        $exception = "App\\Exceptions\\" . ucfirst(strtolower($for)) . "Exception";
        if (
            $nullable &&
            is_null($dto->name) &&
            is_null($dto->class) &&
            is_null($dto->description)
        ) {
            throw new $exception("Sorry! You need to provide at least a name, class or description in order to update this {$for}.", 422);
        }

        if (is_null($dto->name) && !$nullable)
        {
            throw new $exception("Sorry! The name of the {$for} is required.", 422);
        }
        
        if (
            Permission::query()->whereName($dto->name)->exists()
        ) {
            throw new $exception("Sorry! The name '{$dto->name}' has already been taken.", 422);
        }
        
        if (
            !is_null($dto->class) && 
            !class_exists($dto->class)
        ) {
            throw new $exception("Sorry! The class of the objects to which the {$for} applies, has to exist or be null.", 422);
        }
        
        if (
            !is_null($dto->class) && 
            !in_array($dto->class, Permission::AUTHORIZEDCLASSES)
        ) {
            throw new $exception("Sorry! The class you provided is not allowed.", 422);
        }
    }
}