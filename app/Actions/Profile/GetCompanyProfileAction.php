<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Models\Profile;

class GetCompanyProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO): Profile
    {
        return $profileDTO->profileable->profile;
    }
}