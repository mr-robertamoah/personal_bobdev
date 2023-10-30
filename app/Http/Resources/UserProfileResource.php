<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $this->user;

        $ownedCompanies = $user->addedCompanies;
        $memberingCompanies = $user->memberingCompanies();
        $administeringCompanies = $user->administeringCompanies();

        $facilitatorProjects = $user->facilitatorProjects();
        $learnerProjects = $user->learnerProjects();
        $sponsoredProjects = $user->sponsoredProjects();
        $parentProjects = $user->parentProjects();
        $companyProjects = $user->companyProjects();
        $ownedProjects = $this->ownedProjects();

        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),

            'wards' => ProfileUserResource::collection($user->wards),
            'parents' => ProfileUserResource::collection($user->parents),

            'ownedCompanies' => ProfileCompanyResource::collection($ownedCompanies->take(5)),
            'memberingCompanies' => ProfileCompanyResource::collection($memberingCompanies->take(5)),
            'administeringCompanies' => ProfileCompanyResource::collection($administeringCompanies->take(5)),
            'ownedCompaniesCount' => $ownedCompanies->count(),
            'memberingCompaniesCount' => $memberingCompanies->count(),
            'administeringCompaniesCount' => $administeringCompanies->count(),

            'facilitatorProjects' => ProfileProjectResource::collection($facilitatorProjects->take(5)),
            'learnerProjects' => ProfileProjectResource::collection($learnerProjects->take(5)),
            'sponsoredProjects' => ProfileProjectResource::collection($sponsoredProjects->take(5)),
            'parentProjects' => ProfileProjectResource::collection($parentProjects->take(5)),
            'companyProjects' => ProfileProjectResource::collection($companyProjects->take(5)),
            'ownedProjects' => ProfileProjectResource::collection($ownedProjects->take(5)),
            'facilitatorProjectsCount' => $facilitatorProjects->count(),
            'learnerProjectsCount' => $learnerProjects->count(),
            'sponsoredProjectsCount' => $sponsoredProjects->count(),
            'parentProjectsCount' => $parentProjects->count(),
            'companyProjectsCount' => $companyProjects->count(),
            'ownedProjectsCount' => $ownedProjects->count(),
        ];
    }
}
