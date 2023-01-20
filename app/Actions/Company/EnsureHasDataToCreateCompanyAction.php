<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;
use App\Models\Company;

class EnsureHasDataToCreateCompanyAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        if (is_null($companyDTO->name) || is_null($companyDTO->alias)) {
            throw new CompanyException("Sorry! Name and alias of the company is required to create a company.");
        }

        if (strlen($companyDTO->alias) < Company::ALIASLENGTH) {
            throw new CompanyException("Sorry! The company alias provided must have at least 8 characters.");
        }

        if (is_null(Company::where('alias', $companyDTO->alias)->first())) {
            return;
        }

        throw new CompanyException("Sorry! Company with an alias {$companyDTO->alias} already exists.");
    }
}