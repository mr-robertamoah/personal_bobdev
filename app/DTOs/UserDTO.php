<?php

namespace App\DTOs;

use App\Models\User;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class UserDTO extends BaseDTO
{
    public ?User $user = null;
    public ?User $currentUser = null;
    public ?string $username = null;
    public ?string $dob = null;
    public ?string $userId = null;
    public ?string $firstName = null;
    public ?string $surname = null;
    public ?string $otherNames = null;
    public ?string $email = null;
    public ?string $gender = null;
    public ?string $currentPassword = null;
    public ?string $password = null;
    public ?string $passwordConfirmation = null;
    
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