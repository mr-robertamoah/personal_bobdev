<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyMemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $member = $this->to;
        if (class_basename($member) == "Company") $member = $this->by;
        
        return [
            "id" => $this->id,
            "relationshipType" => strtolower($this->relationship_type),
            "member" => new UserResource($member)
        ];
    }
}
