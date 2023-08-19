<?php

namespace App\DTOs;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class RequestDTO extends BaseDTO
{
    public bool $isOfficial = false;
    public ?string $state = null;
    public ?string $type = null;
    public ?string $purpose = null;
    public ?string $userId = null;
    public ?string $userType = null;
    public ?User $user = null;
    public ?string $fromId = null;
    public ?string $fromType = null;
    public User|Company|null $from = null;
    public ?string $toId = null;
    public ?string $toType = null;
    public User|Company|null $to = null;
    public ?string $forId = null;
    public ?string $forType = null;
    public Project|Company|Model|null $for = null;
    
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