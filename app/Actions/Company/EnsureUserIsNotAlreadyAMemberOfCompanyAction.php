<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Exceptions\CompanyException;
use App\Models\Company;
use App\Models\User;

class EnsureUserIsNotAlreadyAMemberOfCompanyAction extends Action
{
    public function execute(Company $company, User $user)
    {
        if ($company->isNotMember($user) && $company->isNotOfficial($user)) {
            return;
        }

        throw new CompanyException("Sorry! {$user->name} is already a member of {$company->name} company.");
    }
}