<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class GetModelAction extends Action
{
    public function execute(?string $class, string|int|null $classId): ?Model
    {
        if (is_null($class) || is_null($classId)) {
            return null;
        }

        $class = "App\\Models\\" . ucfirst(strtolower($class));
        if (!class_exists($class))
        {
            return null;
        }

        return $class::find($classId);
    }
}