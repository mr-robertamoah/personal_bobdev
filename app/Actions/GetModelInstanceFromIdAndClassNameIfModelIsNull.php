<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

class GetModelInstanceFromIdAndClassNameIfModelIsNull extends Action
{
    public function execute(int|string|null $id, ?string $type, ?Model $model): Model|null
    {
        if ($model) {
            return $model;
        }

        $class = $this->getModelClass($type);
        
        if (! class_exists($class)) {
            return null;
        }
        
        return $class::find($id);
    }

    private function getModelClass(?string $modelName): string|null
    {
        if (is_null($modelName)) {
            return null;
        }
        
        if (class_exists($modelName)) {
            return $modelName;
        }

        return "App\\Models\\" . ucfirst(strtolower($modelName));
    }
}