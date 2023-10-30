<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Models\ProjectSession;

class CreateProjectSessionAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO)
    {
        return $projectSessionDTO->addedby->addedProjectSessions()->create([
            'name' => $projectSessionDTO->name,
            'project_id' => $projectSessionDTO->project->id,
            'description' => $projectSessionDTO->description,
            'day_of_week' => $projectSessionDTO->dayOfWeek,
            'start_date' => $projectSessionDTO->startDate?->toDateTimeString(),
            'end_date' => $projectSessionDTO->endDate?->toDateTimeString(),
            'start_time' => $projectSessionDTO->startTime?->toTimeString(),
            'end_time' => $projectSessionDTO->endTime?->toTimeString(),
            'type' => $projectSessionDTO->type,
            'period' => $projectSessionDTO->period,
        ]);
    }
}