<?php

namespace App\DTOs;

use App\Models\Skill;
use App\Models\SkillType;
use App\Models\User;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class SkillDTO extends BaseDTO
{
    public ?SkillType $skillType = null;
    public ?string $skillTypeId = null;
    public ?Skill $skill = null;
    public ?string $skillId = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?string $userId = null;
    public ?User $user = null;
    
    protected array $dtoDataKeys = ['name', 'description', 'skillTypeId' => 'skill_type_id'];

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