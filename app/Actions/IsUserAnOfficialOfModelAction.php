<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IsUserAnOfficialOfModelAction extends Action
{
    public function execute(User $user, Model $model): bool
    {
        if (!method_exists($model, 'isOfficial')) {
            return false;
        }

        return $model->isOfficial($user);
    }
}