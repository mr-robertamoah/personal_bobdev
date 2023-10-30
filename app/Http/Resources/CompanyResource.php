<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'about' => $this->about,
            "owner" => new UserResource($this->owner),
            "noOfMembers" => $this->membersQuery()->count(),
            "noOfOfficials" => $this->officialsQuery()->count(),
            "noOfProjects" => $this->addedProjects()->count(),
            "noOfSponsorships" => $this->sponsoredProjectsQuery()->count(),
            // TODO mini resource, normal and detailed also for project
        ];
    }
}
