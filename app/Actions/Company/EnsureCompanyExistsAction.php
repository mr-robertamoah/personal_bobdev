<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;

class EnsureCompanyExistsAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        if ($companyDTO->company) {
            return;
        }

        throw new CompanyException("Sorry! You need to provide a company to be able to perform this action.");
    }
}