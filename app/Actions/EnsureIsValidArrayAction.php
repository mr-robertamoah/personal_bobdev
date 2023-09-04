<?php

namespace App\Actions;

use App\Actions\Action;
use App\DTOs\RequestableArrayValidationDTO;

class EnsureIsValidArrayAction extends Action
{
    public function execute(RequestableArrayValidationDTO $dto)
    {
        if (count($dto->items) == 0) {
            throw new $dto->exception("Sorry! The users and their respective {$dto->itemsName} type must be specified.");
        }
        
        if (array_is_list($dto->items)) {
            throw new $dto->exception("Sorry! You need to provide a list of user ids pointing to the {$dto->itemsName} type you wish to establish with the company/project.");
        }

        $values = array_values($dto->items);
        
        if (
            count(array_filter(array_keys($dto->items), 'is_string')) == 0 &&
            (
                count(array_filter($values, 'is_string')) + 
                count(array_filter($values, 'is_array'))
            ) == count($values) 
        ) {
            return;
        }

        throw new $dto->exception("Sorry! The user ids must point to respective {$dto->itemsName} types.");
    }
}