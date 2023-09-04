<?php

namespace App\Actions;

use MrRobertAmoah\DTO\BaseDTO;

class SetStartAndEndDatesAction extends Action
{
    public function execute(BaseDTO $dto): array
    {
        $dates = [];

        if ($dto->startDate) {
            $dates['start_date'] = TransformDateForDatabaseAction::make()->execute($dto->startDate);
        }

        if ($dto->endDate) {
            $dates['end_date'] = TransformDateForDatabaseAction::make()->execute($dto->endDate);
        }

        return $dates;
    }   
}