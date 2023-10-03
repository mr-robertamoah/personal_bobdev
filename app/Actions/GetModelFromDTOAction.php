<?php

namespace App\Actions;

use App\Models\User;
use MrRobertAmoah\DTO\BaseDTO;

class GetModelFromDTOAction extends Action
{
    public function execute(
        BaseDTO $dto,
        ?string $property = "user",
        ?string $model = "user",
    ) {
        if (is_null($model)) return $dto->$property;
        
        $modelId = $property . 'Id';
        $modelClass = $model;
        if (!str_contains($model, "App\\Models\\"))
        {
            $modelClass = "App\\Models\\" . ucfirst(strtolower($model));
        }
        
        return $dto->$property ? $dto->$property :
            ($dto->$modelId ? $modelClass::find($dto->$modelId) : null);
    }
}