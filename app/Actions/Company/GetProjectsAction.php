<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Models\Project;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Collection;

class GetProjectsAction extends Action
{
    public function execute(CompanyDTO $companyDTO) : Collection
    {
        $query = Project::query();

        $query->latest();

        $type = strtolower($companyDTO->type);

        if ($type == "added") 
            $query->whereAddedby($companyDTO->company);

        if ($type == "sponsored") 
            $query->whereIsSponsor($companyDTO->company);
            
        if ($type == "all") 
            $query->whereIsSponsor($companyDTO->company)
                ->orWhere(function($query) use ($companyDTO) {
                    $query->whereAddedby($companyDTO->company);
                });

        return $query->get();
    }
}