<?php 

namespace App\Services;

use App\DTOs\ProfileDTO;
use App\Exceptions\NotProfileableModelException;
use App\Exceptions\ProfileableNotAvailableException;
use App\Exceptions\ProfileAlreadyExistsException;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProfileService extends Service
{
    public static function initProfileCreation(User $user) 
    {
        (new static)->createProfile(
            ProfileDTO::fromArray([
                'settings' => [],
                'about' => null,
                'profileable' => $user
            ])
        );
    }

    public function createProfile(ProfileDTO $profileDTO) : Profile
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