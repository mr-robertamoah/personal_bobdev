<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;

class EnsureUserIsOfficialOfCompanyAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        EnsureIsOfficialOfCompanyAction::make()->execute(
            $companyDTO->company,
            $companyDTO->user
        );
    }
}