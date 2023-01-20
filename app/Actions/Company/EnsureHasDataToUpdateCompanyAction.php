<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;

class EnsureHasDataToUpdateCompanyAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        if (is_null($companyDTO->name) && is_null($companyDTO->about)) {
            throw new CompanyException("Sorry! There is not enough data to update the information the company with name {$companyDTO->company->name}.");
        }
    }
}