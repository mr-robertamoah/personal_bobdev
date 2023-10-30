<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;
use Carbon\Carbon;

class EnsureValidTimeDataAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO, bool $nullable = false)
    {
        if (
            $nullable &&
            (is_null($projectSessionDTO->startTime) ||
            is_null($projectSessionDTO->startTime))
        ) return;
        
        if (
            Carbon::parse($projectSessionDTO->endTime)->subHour()
                ->greaterThanOrEqualTo($projectSessionDTO->startTime)
        ) return;

        throw new ProjectSessionException("Sorry! The end time for the session should be at least an hour after the start time.", 422);
    }
}