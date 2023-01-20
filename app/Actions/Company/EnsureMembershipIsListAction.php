<?php

namespace App\Actions\Company;

use App\Actions\Action;
use App\DTOs\CompanyDTO;
use App\Exceptions\CompanyException;

class EnsureMembershipIsListAction extends Action
{
    public function execute(CompanyDTO $companyDTO)
    {
        if (count($companyDTO->memberships) == 0) {
            throw new CompanyException('Sorry! The users and their respective membership type must be specified.');
        }
        
        if (array_is_list($companyDTO->memberships)) {
            throw new CompanyException('Sorry! You need to provide a list of user ids pointing to the membership type you wish to establish with the company.');
        }

        $values = array_values($companyDTO->memberships);
        
        if (
            count(array_filter(array_keys($companyDTO->memberships), 'is_string')) == 0 &&
            count(array_filter($values, 'is_string')) == count($values) 
        ) {
            return;
        }

        throw new CompanyException('Sorry! The user ids must point to respective membership types.');
    }
}