<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;
use Carbon\Carbon;

class EnsureDatesAreWithinProjectDatesAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO)
    {
        if (
            $projectSessionDTO->project->startDate && 
            Carbon::parse($projectSessionDTO->project->startDate)
                ->lessThan($projectSessionDTO->startDate)
        ) {
            throw new ProjectSessionException("Sorry! The start date for the session should be after {$projectSessionDTO->project->startDate}.", 422);
        }
        
        if (
            $projectSessionDTO->project->endDate && 
            Carbon::parse($projectSessionDTO->project->endDate)
                ->greaterThanOrEqualTo($projectSessionDTO->endDate)
        ) {
            throw new ProjectSessionException("Sorry! The end date for the session should be before or the same as {$projectSessionDTO->project->startDate}.", 422);
        }
    }
}