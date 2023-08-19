<?php

namespace App\Actions\Users;

use App\Actions\Action;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IsUserModelAction extends Action
{
    public function execute(Model $model): bool
    {
        return $model::class == User::class;
    }
}