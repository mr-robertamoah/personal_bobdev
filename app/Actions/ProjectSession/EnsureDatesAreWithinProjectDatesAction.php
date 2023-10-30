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
        $startDate = $projectSessionDTO->projectSession ? 
            $projectSessionDTO->projectSession->project->startDate :
            $projectSessionDTO->project?->startDate;
        $endDate = $projectSessionDTO->projectSession ? 
            $projectSessionDTO->projectSession->project->endDate :
            $projectSessionDTO->project?->endDate;
        if (
            now()
                ->greaterThan($projectSessionDTO->startDate)
        ) {
            throw new ProjectSessionException("Sorry! The start date for the session should be on or after today.", 422);
        }
        
        if (
            $startDate && 
            Carbon::parse($startDate)
                ->greaterThan($projectSessionDTO->startDate)
        ) {
            $toDateTimeString = Carbon::parse($startDate)->toDateTimeString();
            throw new ProjectSessionException("Sorry! The start date for the session should come after or on {$toDateTimeString} date.", 422);
        }
        
        if (
            $endDate && 
            Carbon::parse($endDate)
                ->lessThan($projectSessionDTO->endDate)
        ) {
            $toDateTimeString = Carbon::parse($endDate)->toDateTimeString();
            throw new ProjectSessionException("Sorry! The end date for the session should come before or on {$toDateTimeString} date.", 422);
        }
        
        if (
            $projectSessionDTO->startDate &&
            $projectSessionDTO->endDate &&
            $projectSessionDTO->endDate
                ->lessThan($projectSessionDTO->startDate)
        ) {
            throw new ProjectSessionException("Sorry! The end date for the session should be either the same day as or after the start date.", 422);
        }
    }
}