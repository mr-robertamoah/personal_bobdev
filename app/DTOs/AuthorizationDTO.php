<?php

namespace App\DTOs;

use App\Models\Authorization;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class AuthorizationDTO extends BaseDTO
{
    public string|null $name = null;
    public string|int|null $userId = null;
    public string|int|null $authorizableId = null;
    public string|int|null $mainAuthorizationId = null;
    public string|int|null $authorizationId = null;
    public string|int|null $authorizedId = null;
    public string|null $authorizableType = null;
    public string|null $authorizationType = null;
    public Company|Project|null $authorizable = null;
    public Authorization|null $mainAuthorization = null;
    public Permission|Role|null $authorization = null;
    public User|null $user = null;
    public User|null $authorized = null;
    public string|int|null $page = null;
    
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

    public function isForNextPage() : bool
    {
        return !is_null($this->page);
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