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
        return [
            'id' => $this->id,
            'userId' => $this->user->id,
            'facilitatorProjects' => $this->when(
                $this->user->isFacilitator(), 
                $this->facilitatorProjects()
            ),
            'learnerProjects' => $this->when(
                $this->user->isLearner(), 
                $this->learnerProjects()
            ),
            'sponsorProjects' => $this->when(
                $this->user->isSponsor(), 
                $this->sponsorProjects()
            ),
            'parentProjects' => $this->when(
                $this->user->isparent(), 
                $this->parentProjects()
            ),
            'ownedProjects' => $this->when(
                $this->user->hasOwnedProjects(), 
                $this->ownedProjects()
            )
        ];
    }
}
