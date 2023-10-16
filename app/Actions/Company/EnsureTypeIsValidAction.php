<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\CompanyException;
use App\Models\Company;

class EnsureTypeIsValidAction extends Action
{
    public function execute(
        CompanyDTO $companyDTO, string $for = "members"
    ) {
        $types = RelationshipTypeEnum::types();
        if ($for == "projects")
            $types = Company::PROJECTTYPES;

        if (
            in_array(
            strtolower($companyDTO->type), 
            $types
        )) return;

        $types = implode(", ", $types);
        throw new CompanyException("Sorry, the type provided should be any of the following: {$types}", 422);
    }
}