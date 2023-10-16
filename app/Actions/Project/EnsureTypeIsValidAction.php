<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Actions\BuildGetAuthorizableQueryAction;
use App\DTOs\ProjectDTO;
use App\Enums\PaginationEnum;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\ProjectException;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class EnsureTypeIsValidAction extends Action
{
    public function execute(ProjectDTO $projectDTO)
    {
        if (in_array(
            strtolower($projectDTO->type), 
            ProjectParticipantEnum::types()
        )) return;

        $types = implode(", ", ProjectParticipantEnum::types());
        throw new ProjectException("Sorry, the type provided should be any of the following: {$types}", 422);
    }
}