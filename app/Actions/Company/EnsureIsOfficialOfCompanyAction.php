<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;
use App\Models\Company;
use App\Models\User;

class EnsureIsOfficialOfCompanyAction extends Action
{
    public function execute(Company $company, User $user)
    {
        if (
            $company->isOfficial($user) ||
            $user->isAdmin()
        ) {
            return;
        }

        throw new CompanyException("Sorry! You are not authorized to perform this action on company with name {$company->name}.");
    }
}