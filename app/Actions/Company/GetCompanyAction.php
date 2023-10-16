<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Models\Company;

class GetCompanyAction extends Action
{
    public function execute(CompanyDTO $companyDTO) : Company
    {
        return $companyDTO->company
            ->with([
                "participations", "addedByRelations", "addedToRelations", "owner",
                "addedProjects"
            ])->first();
    }
}