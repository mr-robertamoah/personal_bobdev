<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectSessionResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "startDate" => $this->start_date->diffForHumans(),
            "endDate" => $this->end_date->diffForHumans(),
            "startTime" => $this->start_time->diffForHumans(),
            "endTime" => $this->end_time->diffForHumans(),
            "createdAt" => $this->created_at->diffForHumans(),
        ];
    }
}
