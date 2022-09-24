<?php

namespace App\Services;

use App\DTOs\ActivityDTO;
use App\Exceptions\ActivityException;
use App\Models\Activity;
use App\Models\User;

class ActivityService
{
    public function createActivity(ActivityDTO $activityDTO)
    {
        if ($this->doesNotHaveAppropriateData($activityDTO)) {
            throw new ActivityException('Sorry! You do not have the needed information to perform this action.');
        }

        $activity = $activityDTO->performedby->activities()->create(['action' => $activityDTO->action]);

        $activity->performedon()->associate($activityDTO->performedon);

        $activity->save();

        return $activity;
    }

    public function deleteActivity(User $user, $activityId)
    {
        if (!$activityId) {
            throw new ActivityException("Sorry! The id of the activity is required to perform this action.");
        }
        
        if (!$user->isAdmin()) {
            throw new ActivityException("Sorry! You cannot delete the activity with id {$activityId}.");
        }

        return Activity::find($activityId)?->delete();
    }

    private function hasAppropriateData(ActivityDTO $activityDTO)
    {
        if (!$activityDTO->performedby || !$activityDTO->performedon || !$activityDTO->action) {
            return false;
        }

        return true;
    }

    private function doesNotHaveAppropriateData(ActivityDTO $activityDTO)
    {
        return !$this->hasAppropriateData($activityDTO);
    }
}