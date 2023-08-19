<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;

class EnsureProfileableExistsAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {
        if (!is_null($profileDTO->profileable)) {
            return;
        }
        
        throw new ProfileException(
            "Sorry! Cannot {$profileDTO->action} profile because an owner was not found."
        );
    }
}