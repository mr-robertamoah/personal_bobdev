<?php

namespace App\DTOs;

use App\Models\Company;
use App\Models\User;
use App\Traits\AuthorizableDTOTrait;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class CompanyDTO extends BaseDTO
{
    use AuthorizableDTOTrait;
    
    public ?User $user = null;
    public ?User $owner = null;
    public ?Company $company = null;
    public User|Company|null $by = null;
    public User|Company|null $to = null;
    public ?string $relationshipType = null;
    public ?string $type = null;
    public ?string $purpose = null;
    public ?string $alias = null;
    public ?string $about = null;
    public ?string $userId = null;
    public ?array $memberships = [];
    public ?string $companyId = null;
    
    /**
     * assign data (filled or validated) to the dto properties as an 
     * addition to the fromRequest function.
     *
     * @param  Illuminate\Http\Request  $request
     * @return MrRobertAmoah\DTO\BaseDTO
     */
    protected function fromRequestExtension(Request $request) : self
    {
        return $this;
    }

    /**
     * assign values of keys of an array to the corresponding dto properties 
     * as an additional function for the fromData function.
     *
     * @param  array  $data
     * @return MrRobertAmoah\DTO\BaseDTO
     */
    protected function fromArrayExtension(array $data = []) : self
    {
        return $this;
    }

    /**
    * uncomment and use this function if you want to 
    * customize the key and value pairs
    * to be used to create your dto and still get the 
    * other features of the dto
    */
//    public function requestToArray($request)
//    {
//       return [];
//    }
}