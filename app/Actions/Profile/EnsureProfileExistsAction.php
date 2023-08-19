<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\Actions\GetModelsClassNameInLowerCaseAction;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;

class EnsureProfileExistsAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {
        if (!is_null($profileDTO->profile)) {
            return;
        }

        $type = GetModelsClassNameInLowerCaseAction::make()->execute($profileDTO->profileable);
        
        throw new ProfileException("Sorry! Profile for {$type} with name {$profileDTO->profileable->name} was not found.");
    }
}