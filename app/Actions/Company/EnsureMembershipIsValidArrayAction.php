<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Actions\EnsureIsValidArrayAction;
use App\DTOs\CompanyDTO;
use App\DTOs\RequestableArrayValidationDTO;
use App\Exceptions\CompanyException;

class EnsureMembershipIsValidArrayAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        EnsureIsValidArrayAction::make()->execute(
            RequestableArrayValidationDTO::new()->fromArray([
                "items" => $companyDTO->memberships,
                "itemsName" => "membership",
                "exception" => CompanyException::class
            ])
        );
    }
}