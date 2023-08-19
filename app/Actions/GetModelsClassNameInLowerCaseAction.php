<?php

namespace App\Actions;

use Illuminate\Database\Eloquent\Model;

class GetModelsClassNameInLowerCaseAction extends Action
{
    public function execute(Model $model): string
    {
        $class = $model::class;

        $className = substr( strrchr($class, "\\"), 1);

        if ($className) {
            return strtolower($className);
        }

        return "";
    }
}