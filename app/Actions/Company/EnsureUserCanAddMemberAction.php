<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;

class EnsureUserCanAddMemberAction extends Action
{
    public function execute(CompanyDTO $companyDTO, bool $isRequest = false)
    {
        if ($companyDTO->user->isAdmin() && !$isRequest) {
            return;
        }

        if ($companyDTO->company->isOwner($companyDTO->user)) {
            return;
        }
        
        if (
            $companyDTO->company->isManager($companyDTO->user) && 
            in_array(
                strtolower($companyDTO->relationshipType), 
                RelationshipTypeEnum::COMPANYMEMBERALIASES
            )
        ) {
            return;
        }

        throw new CompanyException("Sorry! You are not authorized to perform this action on the company with name {$companyDTO->company->name}.");
    }
}