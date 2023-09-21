<?php

namespace App\Actions;

use App\Actions\Action;
use App\DTOs\ProjectSessionDTO;
use App\Exceptions\ProjectSessionException;
use Carbon\Carbon;
use MrRobertAmoah\DTO\BaseDTO;

class MakeDatesCarbonAction extends Action
{
    public function execute(BaseDTO $dto) : BaseDTO
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
        
        return $dto;
    }
}