<?php

namespace App\Actions;

use MrRobertAmoah\DTO\BaseDTO;

class SetAuthorizationClassAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        string $property = "class"
    ) : BaseDTO {
        if (is_null($dto->$property)) return $dto;
        
        $method = "with" . ucfirst(strtolower($property));

        return $dto->$method(
            "App\\Models\\" . ucfirst(strtolower($dto->$property))
        );
    }
}