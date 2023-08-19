<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;

class EnsureProfileableIsAppropriateModelAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {
        if ($this->isProfileable($profileDTO->profileable)) {
            return;
        }
        
        throw new ProfileException("Sorry 😕! You can only create a profile for either a user or company.");
    }

    private function isProfileable(Model $model) : bool
    {
        return in_array($model::class, Profile::PROFILEABLECLASSES);
    }
}