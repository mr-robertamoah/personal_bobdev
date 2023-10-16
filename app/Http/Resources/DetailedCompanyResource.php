<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailedCompanyResource extends JsonResource
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
            "members" => CompanyMemberResource::collection($this->members()->take(5)),
            "officials" => CompanyMemberResource::collection($this->officials()->take(5)),
            "projects" => MiniProjectResource::collection($this->addedProjects->take(5)),
            "sponsorships" => ProjectResource::collection($this->sponsoredProjects()->take(5)),
        ];
    }
}
