<?php

namespace App\Actions\ProjectSession;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Enums\ProjectSessionPeriodEnum;
use App\Enums\ProjectSessionTypeEnum;
use App\Exceptions\ProjectSessionException;

class SetEnumValuesAction extends Action
{
    public function execute(ProjectSessionDTO $projectSessionDTO, bool $nullable = false) : ProjectSessionDTO
    {
        if (is_null($projectSessionDTO->period) && !$nullable) {
            throw new ProjectSessionException('Sorry! you need to set the period for the session.', 422);
        }

        $projectSessionDTO = $projectSessionDTO->withPeriod(
            ProjectSessionPeriodEnum::getValueOf($projectSessionDTO->period)
        );
        
        if (is_null($projectSessionDTO->type) && !$nullable) {
            throw new ProjectSessionException('Sorry! you need to set the type for the session.', 422);
        }

        $projectSessionDTO = $projectSessionDTO->withType(
            ProjectSessionTypeEnum::getValueOf($projectSessionDTO->type)
        );
        
        return $projectSessionDTO;
    }
}