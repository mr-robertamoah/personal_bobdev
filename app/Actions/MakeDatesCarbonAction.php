<?php

namespace App\Actions;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;
use Carbon\Carbon;
use MrRobertAmoah\DTO\BaseDTO;

class MakeDatesCarbonAction extends Action
{
    public function execute(
        BaseDTO $dto, 
        bool $hasTime = false,
        bool $nullable = false,
    ) : BaseDTO
    {
        if ($dto->startDate) {
            $dto = $dto->withStartDate(
                Carbon::parse($dto->startDate)
            );
        }
        
        if ($dto->endDate) {
            $dto = $dto->withEndDate(
                Carbon::parse($dto->endDate)
            );
        }

        if (!$hasTime) return $dto;
        
        if (
            $nullable &&
            (is_null($dto->startTime) ||
            is_null($dto->endTime))
        ) return $dto;

        $dto = $dto->withStartTime(
            Carbon::parse($dto->startTime ?: 0)
        );

        $dto = $dto->withEndTime(
            Carbon::parse($dto->endTime ?: 24 * 3600 - 1)
        );
        
        return $dto;
    }
}