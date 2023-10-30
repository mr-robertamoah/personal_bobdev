<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
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
            "public" => (bool) $this->public,
            "description" => $this->description,
            "class" => is_null($this->class) ? null: class_basename($this->class),
            "updatedAt" => $this->updated_at,
            "createdAt" => $this->created_at->diffForHumans(),,
        ];
    }
}
