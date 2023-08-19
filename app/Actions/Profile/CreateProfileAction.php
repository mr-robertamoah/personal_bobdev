<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;

class CreateProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {   
        $profile = $profileDTO->profileable->profile()->create([
            $profileDTO->getData()
        ]);

        return $profile;
    }
}