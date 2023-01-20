<?php

namespace App\Actions\Activity;

use App\Actions\Action;
use App\DTOs\ActivityDTO;
use App\Services\ActivityService;

class AddActivityAction extends Action
{
    public function execute(ActivityDTO $activityDTO)
    {
        (new ActivityService)->createActivity($activityDTO);
    }
}