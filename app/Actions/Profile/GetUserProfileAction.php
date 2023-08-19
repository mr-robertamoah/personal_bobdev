<?php

namespace App\Actions\Profile;

use App\Actions\Action;
use App\DTOs\ProfileDTO;
use App\Models\Profile;

class GetUserProfileAction extends Action
{
    public function execute(ProfileDTO $profileDTO): Profile
    {
        $profile = GetUserProfileWithLoadedUserTypesAction::make()->execute($profileDTO->profileable);

        if ($profile->isFacilitator()) {
            $profile->loadFacilitatorProjects();
        }

        if ($profile->isLearner()) {
            $profile->loadLearnerProjects();
        }

        if ($profile->isSponsor()) {
            $profile->loadSponsorProjects();
        }
// make this work and learn about what is being loaded
        if ($profile->isParent()) {
            $profile->loadParentProjects();
        }

        return $profile;
    }
}