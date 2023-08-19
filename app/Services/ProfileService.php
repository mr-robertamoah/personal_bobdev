<?php 

namespace App\Services;

use App\Actions\GetModelInstanceFromIdAndClassNameIfModelIsNull;
use App\Actions\Profile\CreateProfileAction;
use App\Actions\Profile\EnsureDoesNotAlreadyHaveProfileAction;
use App\Actions\Profile\ProfileableHasProfileAction;
use App\Actions\Profile\EnsureProfileableExistsAction;
use App\Actions\Profile\EnsureProfileableIsAppropriateModelAction;
use App\Actions\Profile\GetCompanyProfileAction;
use App\Actions\Profile\GetProfileWithLoadedAccountsAction;
use App\Actions\Profile\GetUserProfileAction;
use App\Actions\Profile\ProfileableDoesntHaveProfileAction;
use App\Actions\Users\FindUserByIdAction;
use App\Actions\Users\IsUserModelAction;
use App\DTOs\ProfileDTO;
use App\Exceptions\ProfileException;
use App\Models\Company;
use App\Models\Profile;
use App\Models\User;

class ProfileService extends Service
{
    public function initProfileCreation(User|Company $profileable): Profile
    {
        return CreateProfileAction::make()->execute(
            ProfileDTO::fromArray([
                'profileable' => $profileable
            ])
        );
    }

    public function createProfile(ProfileDTO $profileDTO)
    {
        $profileDTO = $profileDTO->WithProfileable(
            GetModelInstanceFromIdAndClassNameIfModelIsNull::make()->execute(
                id: $profileDTO->profileableId,
                type: $profileDTO->profileableType,
                model: $profileDTO->profileable
            )
        );

        $profileDTO = $profileDTO->withAction("create");

        EnsureProfileableExistsAction::make()->execute($profileDTO);

        EnsureProfileableIsAppropriateModelAction::make()->execute($profileDTO);

        EnsureDoesNotAlreadyHaveProfileAction::make()->execute($profileDTO);
        
        return CreateProfileAction::make()->execute($profileDTO);
    }

    public function getProfile(ProfileDTO $profileDTO): Profile
    {
        $profileDTO = $profileDTO->WithProfileable(
            GetModelInstanceFromIdAndClassNameIfModelIsNull::make()->execute(
                id: $profileDTO->profileableId,
                type: $profileDTO->profileableType,
                model: $profileDTO->profileable
            )
        );

        $profileDTO = $profileDTO->withAction("get");

        EnsureProfileableExistsAction::make()->execute($profileDTO);

        if (ProfileableDoesntHaveProfileAction::make()->execute($profileDTO)) {
            return CreateProfileAction::make()->execute($profileDTO);
        }

        if (IsUserModelAction::make()->execute($profileDTO->profileable)) {
            return GetUserProfileAction::make()->execute($profileDTO);
        }

        return GetCompanyProfileAction::make()->execute($profileDTO);
    }
}