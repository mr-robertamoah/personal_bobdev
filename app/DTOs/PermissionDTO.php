<?php

namespace App\DTOs;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class PermissionDTO extends BaseDTO
{
    public ?Permission $permission = null;
    public ?Role $role = null;
    public ?User $user = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $class = null;
    public string|int|null $userId = null;
    public string|int|null $permissionId = null;
    public array $permissionIds = [];
    public string|int|null $roleId = null;

    //set properties that correspond with request inputs
    
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