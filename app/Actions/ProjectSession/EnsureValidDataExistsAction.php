<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Enums\ProjectSessionPeriodEnum;
use App\Enums\ProjectSessionTypeEnum;
use App\Exceptions\ProjectSessionException;

class EnsureValidDataExistsAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO, bool $nullable = false)
    {
        if (
            $nullable &&
            is_null($projectSessionDTO->name) &&
            is_null($projectSessionDTO->type) &&
            is_null($projectSessionDTO->dayOfWeek) &&
            is_null($projectSessionDTO->period) &&
            is_null($projectSessionDTO->startDate) &&
            is_null($projectSessionDTO->endDate) &&
            is_null($projectSessionDTO->startTime) &&
            is_null($projectSessionDTO->endTime)
        ) {
            throw new ProjectSessionException('Sorry! You cannot update a project session without setting any of the following: name, type, period, start date and end date.', 422);
        }

        if (is_null($projectSessionDTO->period) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to select a valid period for the session.', 422);
        }

        if (is_null($projectSessionDTO->period) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to select a day on which the session will be held.', 422);
        }

        if (is_null($projectSessionDTO->name) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to select a name for the session.', 422);
        }
        
        if (is_null($projectSessionDTO->type) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to select a valid type for the session.', 422);
        }
        
        $startDate = $nullable ? 
            $projectSessionDTO->projectSession->project->startDate :
            $projectSessionDTO->project->startDate;
        if ($startDate && is_null($projectSessionDTO->startDate) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to set the start date for the session.', 422);
        }
        
        $endDate = $nullable ? 
            $projectSessionDTO->projectSession->project->endDate :
            $projectSessionDTO->project->endDate;
        if ($endDate && is_null($projectSessionDTO->endDate) && !$nullable) {
            throw new ProjectSessionException('Sorry! You need to set the end date for the session.', 422);
        }
    }
}