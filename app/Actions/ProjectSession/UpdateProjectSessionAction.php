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
        ];

        $data = array_filter($data, fn($value) => !is_null($value));

        $projectSessionDTO->projectSession->update($data);

        return $projectSessionDTO->projectSession->refresh();
    }
}