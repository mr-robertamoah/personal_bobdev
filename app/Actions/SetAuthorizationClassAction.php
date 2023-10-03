<?php

namespace App\Actions;

use App\Exceptions\AuthorizationException;
use MrRobertAmoah\DTO\BaseDTO;

class SetAuthorizationClassAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        string $property = "class",
        bool $throwExcepion = false
    ) : BaseDTO {
        if ($dto->isForNextPage()) return $dto;

        if (is_null($dto->$property)) {

            if ($throwExcepion) {
                throw new AuthorizationException("Sorry! {$property} must be given.", 422);
            }

            return $dto;
        }
        
        $method = "with" . ucfirst($property);

        $dto = $dto->$method(
            "App\\Models\\" . ucfirst($dto->$property)
        );

        if ($throwExcepion && !class_exists($dto->$property)) {
            throw new AuthorizationException("Sorry! {$property} is not valid.", 422);
        }

        return $dto;
    }
}