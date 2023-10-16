<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\DTOs\ProjectDTO;
use Illuminate\Database\Eloquent\Collection;

class GetParticipantsAction extends Action
{
    public function execute(ProjectDTO $projectDTO) : Collection
    {
        $query = $projectDTO->project->participants();

        $query->latest();

        $type = strtolower($projectDTO->type);

        if ($type == "officials") 
            $query->whereOfficial();

        if ($type == "sponsors") 
            $query->whereSponsor();

        return $query->get();
    }
}