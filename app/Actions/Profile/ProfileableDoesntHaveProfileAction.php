<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;

class ProfileableDoesntHaveProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO): bool
    {
        return ! ProfileableHasProfileAction::make()->execute($profileDTO);
    }
}