<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DetailedProjectResource extends JsonResource
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

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'owner' => $ownerType == "Company" ? 
                new CompanyResource($this->addedby) :
                new UserResource($this->addedby),
            'ownerType' => $ownerType,
            'skills' => $this->skills,
            'noOfParticipants' => $this->participants()->count(),
            'participants' => ProjectParticipantResource::collection(
                $this->participants->take(5)
            ),
            // TODO add projectsessions as well as the ones held
            // 'resouces' => $this->when(
            //     $this->canAccessResources($request->user()),
            //     FileResource::collection($this->files),
            //     FileResource::collection($this->publicFiles),
            // )
        ];
    }
}
