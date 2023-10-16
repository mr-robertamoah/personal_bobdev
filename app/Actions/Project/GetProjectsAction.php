<?php

namespace App\Actions\Project;

use App\Actions\Action;
use App\Actions\BuildGetAuthorizableQueryAction;
use App\DTOs\ProjectDTO;
use App\Enums\PaginationEnum;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;

class GetProjectsAction extends Action
{
    public function execute(ProjectDTO $projectDTO) : LengthAwarePaginator
    {
        $query = BuildGetAuthorizableQueryAction::make()->execute(
            Project::query(), $projectDTO
        );

        if ($projectDTO->participant) $query->whereIsParticipant($projectDTO->participant);
        if ($projectDTO->participationType) $query->whereParticipationType($projectDTO->participationType);
        if ($projectDTO->skillName) $query->whereHasSkillWithNameLike($projectDTO->skillName);

        return $query->paginate(PaginationEnum::getUsers->value);
    }
}