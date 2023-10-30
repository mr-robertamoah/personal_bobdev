<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Models\ProjectSession;

class UpdateProjectSessionAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO) : ProjectSession
    {
        $data = [
            'name' => $projectSessionDTO->name,
            'start_date' => $projectSessionDTO->startDate?->toDateTimeString(),
            'end_date' => $projectSessionDTO->endDate?->toDateTimeString(),
            'type' => $projectSessionDTO->type,
            'period' => $projectSessionDTO->period,
            'description' => $projectSessionDTO->description,
            'day_of_week' => $projectSessionDTO->dayOfWeek,
            'start_time' => $projectSessionDTO->startTime?->toTimeString(),
            'end_time' => $projectSessionDTO->endTime?->toTimeString(),
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        $projectSessionDTO->projectSession->update($data);

        return $projectSessionDTO->projectSession->refresh();
    }
}