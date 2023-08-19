<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;

class EnsureDoesNotAlreadyHaveProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {
        if ($profileDTO->profileable->doesntHaveProfile()) {
            return;
        }
        
        throw new ProfileException("Sorry 😏! This user or company already has a profile.");
    }
}