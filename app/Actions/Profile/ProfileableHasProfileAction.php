<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;

class ProfileableHasProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO): bool
    {
        return $profileDTO->profileable->hasProfile();
    }
}