<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\Company;
use App\Models\User;

class EnsureUserIsAlreadyAMemberOfCompanyAction extends Action
{
    public function execute(Company $company, User $user, string $relationshipType)
    {
        if ($company->isOwner($user)) {
            throw new CompanyException("Sorry! {$user->name} is the owner and is not a member.");
        }
        
        if ($company->isNotMember($user) && $company->isNotManager($user)) {
            throw new CompanyException("Sorry! {$user->name} must be a member of {$company->name} company.");
        }

        $relationshipType = strtolower($relationshipType);

        if (
            ($company->isMember($user) && in_array($relationshipType, RelationshipTypeEnum::COMPANYMEMBERALIASES)) ||
            ($company->isManager($user) && in_array($relationshipType, RelationshipTypeEnum::COMPANYADMINISTRATORALIASES)) 
        ) {
            return;
        }

        throw new CompanyException("Sorry! {$user->name} is not a {$relationshipType} in the company with name {$company->name}.");
    }
}