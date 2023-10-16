<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\Actions\BuildGetAuthorizableQueryAction;
use App\DTOs\CompanyDTO;
use App\Enums\PaginationEnum;
use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

class GetCompaniesAction extends Action
{
    public function execute(CompanyDTO $companyDTO) : LengthAwarePaginator
    {
        $query = BuildGetAuthorizableQueryAction::make()->execute(
            Company::query(), $companyDTO
        );

        if ($companyDTO->relationshipType) $query->whereIsOfRelationshipType($companyDTO->relationshipType);

        return $query->paginate(PaginationEnum::getUsers->value);
    }
}