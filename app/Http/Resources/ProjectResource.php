<?php

namespace App\Http\Resources;

use App\Models\Company;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $ownerType = class_basename($this->addedby);

        $company = Company::find($request->company_id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'owner' => $ownerType == "Company" ? 
                new CompanyResource($this->addedby) :
                new UserResource($this->addedby),
            'ownerType' => $ownerType,
            'skills' => SkillResource::collection($this->skills),
            'noOfParticipants' => $this->participants()->count(),
            "isSponsor" => $this->when(
                $company,
                $this->isSponsor($company)
            ),
            // TODO skills, sessions, facilitators, learners
        ];
    }
}
