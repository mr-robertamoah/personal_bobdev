<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Models\ProjectSession;

class CreateProjectSessionAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO) : ProjectSession
    {
        return $projectSessionDTO->project->projectSessions()->create([
            'name' => $projectSessionDTO->name,
            'start_date' => $projectSessionDTO->startDate?->toDateTimeString(),
            'end_date' => $projectSessionDTO->endDate?->toDateTimeString(),
            'type' => $projectSessionDTO->type,
            'period' => $projectSessionDTO->period,
        ]);
    }
}