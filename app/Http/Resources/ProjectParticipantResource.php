<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $participantClass = class_basename($this->participant);
        
        return [
            "id" => $this->id,
            "participatingAs" => strtolower($this->participating_as),
            "participant" =>
            $participantClass == "Company" ? 
                new CompanyResource($this->participant) :
                new UserResource($this->participant)
            // TODO add authorizations if owner or admin
        ];
    }

    private function canAccessAuthorizations($request)
    {
        $user = $request->user();

        if (
            $user->isAdmin() ||
            $user->is($this->project->owner)
        ) return true;

        return false;
    }
}
