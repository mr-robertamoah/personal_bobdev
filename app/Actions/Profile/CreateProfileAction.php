<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileableNotAvailableException;
use App\Exceptions\ProfileAlreadyExistsException;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Model;

class CreateProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO)
    {
        if (
            is_null($profileDTO->profileable) ||
            $this->isNotProfileable($profileDTO->profileable)
        ) {
            throw new ProfileableNotAvailableException(
                "Sorry 😕! You can only create a profile for either a user or company."
            );
        }

        if ($profileDTO->profileable->hasProfile()) {
            throw new ProfileAlreadyExistsException(
                "Sorry 😏! This user or company already has a profile"
            );
        }
        
        $profile = $profileDTO->profileable->profile()->create([
            $profileDTO->getData()
        ]);

        return $profile;
    }

    private function isProfileable(Model $model) : bool
    {
        return in_array($model::class, Profile::PROFILEABLECLASSES);
    }

    private function isNotProfileable(Model $model) : bool
    {
        return ! $this->isProfileable($model);
    }
}