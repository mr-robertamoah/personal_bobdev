<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class IsUserAnOwnerOfModelAction extends Action
{
    public function execute(User $user, Model $model): bool
    {
        if (is_null($model->owner)) {
            return false;
        }

        return $model->owner->is($user);
    }
}