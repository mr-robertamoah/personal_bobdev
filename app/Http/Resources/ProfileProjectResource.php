<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileProjectResource extends JsonResource
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
            'description' => $this->description,
            'owner' => [
                "id" => $this->owner->id,
                "name" => $this->owner->name,
                "type" => strtolower(class_basename($this->owner))
            ],
            'skills' => SkillResource::collection($this->skills->take(5)),
            'noOfParticipants' => $this->participants()->count(),
        ];
    }
}
