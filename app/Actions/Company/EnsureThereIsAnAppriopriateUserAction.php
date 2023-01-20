<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;
use App\Models\Company;

class EnsureThereIsAnAppriopriateUserAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        if (
            is_null($companyDTO->user)
        ) {
            throw new CompanyException("Sorry! There must be a user for this action to be performed.");
        }

        if (
            !is_null($companyDTO->user) &&
            !$companyDTO->user->isAdmin() &&
            !is_null($companyDTO->owner)
        ) {
            throw new CompanyException("Sorry! You must be an administrator to be able to create a company on behalf of another user.");
        }
    }
}