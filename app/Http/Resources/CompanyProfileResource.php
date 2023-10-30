<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $company = $this->company;

        $members = $company->members();
        $officials = $company->officials();

        $sponsoredProjects = $company->sponsoredProjects();
        $ownedProjects = $company->addedProjects;

        return [
            "id" => $this->id,
            "company" => new MiniCompanyResource($company),

            'members' => ProfileUserResource::collection($members->take(5)),
            'officials' => ProfileUserResource::collection($officials->take(5)),
            'membersCount' => $members->count(),
            'officialsCount' => $officials->count(),

            'sponsoredProjects' => ProfileProjectResource::collection($sponsoredProjects->take(5)),
            'ownedProjects' => ProfileProjectResource::collection($ownedProjects->take(5)),
            'sponsoredProjectsCount' => $sponsoredProjects->count(),
            'ownedProjectsCount' => $ownedProjects->count(),
        ];
    }
}
