<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\DTOs\RequestDTO;
use App\Exceptions\CompanyException;

class EnsureRequestIsNotFromACompanyOfficialToAnotherAction extends Action
{
    public function execute(RequestDTO | CompanyDTO $dto) : void
    {
        if (
            $this->isNotFromOfficial($dto) ||
            $this->isNotToOfficial($dto)
        ) {
            return;
        }

        $name = $dto::class == CompanyDTO::class ? $dto->company->name : $dto->for->name;
       
        throw new CompanyException("Sorry! Both sender and recepient cannot be officials of the company with name {$name} company.");
    }

    private function isNotFromOfficial(RequestDTO | CompanyDTO $dto)
    {
        if ($dto::class == CompanyDTO::class)
        {
            return !$dto->company->isOfficial($dto->user);
        }

        return !$dto->for->isOfficial($dto->from);
    }

    private function isNotToOfficial(RequestDTO | CompanyDTO $dto)
    {
        if ($dto::class == CompanyDTO::class)
        {
            return !$dto->company->isOfficial($dto->to);
        }

        return !$dto->for->isOfficial($dto->to);
    }
}