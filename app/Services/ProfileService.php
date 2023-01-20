<?php 

namespace App\Services;

use App\Actions\Profile\CreateProfileAction;
use App\Actions\Profile\GetProfileWithLoadedAccountsAction;
use App\Actions\Users\FindUserByIdAction;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;
use App\Models\User;

class ProfileService extends Service
{
    public function initProfileCreation(User $user) 
    {
        CreateProfileAction::make()->execute(
            ProfileDTO::fromArray([
                'settings' => [],
                'about' => null,
                'profileable' => $user
            ])
        );
    }

    public function getUserProfile(ProfileDTO $profileDTO)
    {
        $user = FindUserByIdAction::make()->execute($profileDTO->userId);

        $profile = GetProfileWithLoadedAccountsAction::make()->execute($user);

        if (is_null($profile)) {
            throw new ProfileException("Sorry! Profile for user with name {$user->name} was not found.");
        }

        if ($profile->isFacilitator()) {
            $profile->loadFacilitatorProjects();
        }

        if ($profile->isLearner()) {
            $profile->loadLearnerProjects();
        }

        if ($profile->isSponsor()) {
            $profile->loadSponsorProjects();
        }

        if ($profile->isParent()) {
            $profile->loadParentProjects();
        }

        return $profile;
            
    }
}