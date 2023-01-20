<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\User;

class EnsureUserCanRemoveMemberAction extends Action
{
    public function execute(CompanyDTO $companyDTO, User $member)
    {
        if (
            $companyDTO->company->isOwner($companyDTO->user) && 
            $companyDTO->user->is($member)
        ) {
            throw new CompanyException("Sorry! As the owner of the company, you cannot leave the company.");
        }
        
        if (
            $companyDTO->company->isNotOwner($companyDTO->user) && 
            $companyDTO->user->is($member)
        ) {
            return;
        }

        if ($companyDTO->user->isAdmin()) {
            return;
        }

        if ($companyDTO->company->isOwner($companyDTO->user)) {
            return;
        }
        
        if (
            $companyDTO->company->isManager($companyDTO->user) && 
            in_array(
                $companyDTO->company->getRelationshipAlias($member), 
                RelationshipTypeEnum::COMPANYMEMBERALIASES
            )
        ) {
            return;
        }

        throw new CompanyException("Sorry! You are not authorized to perform this action on the company with name {$companyDTO->company->name}");
    }
}