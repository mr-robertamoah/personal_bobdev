<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Collection;

class GetMembersAction extends Action
{
    public function execute(CompanyDTO $companyDTO) : Collection
    {
        $query = Relation::query()
            ->whereIsRelated($companyDTO->company);

        $query->latest();

        $type = strtolower($companyDTO->type);

        if ($type == "officials") 
            $query->whereOfficial();

        return $query->get();
    }
}