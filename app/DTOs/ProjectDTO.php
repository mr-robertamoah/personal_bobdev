<?php

namespace App\DTOs;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Traits\AuthorizableDTOTrait;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use MrRobertAmoah\DTO\BaseDTO;

class ProjectDTO extends BaseDTO
{
    use AuthorizableDTOTrait;

    public ?Project $project = null;
    public User|Company|null $owner = null;
    public ?string $type = null;
    public ?string $projectId = null;
    public ?array $participations = [];
    public ?string $participationType = null;
    public ?string $participantType = null;
    public ?string $description = null;
    public ?string $skillName = null;
    public string|DateTime|Carbon|null $startDate = null;
    public  string|DateTime|Carbon|null $endDate = null;
    public ?User $addedby = null;
    
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