<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthorizationResource extends JsonResource
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
            "authorizedBy" => new  MiniUserResource($this->user),
            "authorizableType" => class_basename($this->authorizable),
            "authorizableId" => $this->authorizable->id,
            "authorizedId" => $this->authorized->id,
            "authorization" => class_basename($this->authorization) == "Permission" ?
                new PermissionResource($this->authorization) : new RoleResource($this->authorization),
            "updatedAt" => $this->update_at,
            "createdAt" => $this->create_at,
        ];
    }
}
