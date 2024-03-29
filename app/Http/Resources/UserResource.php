<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'firstName' => $this->first_name,
            'surname' => $this->surname,
            'otherNames' => $this->other_names,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'gender' => strtolower($this->gender),
            'userTypes' => UserTypeResource::collection($this->allUserTypes),
            "isAdult" => $this->isAdult(),
            "isAdmin" => $this->when(
                $this->isAdmin() || $this->is($request->user()),
                $this->isAdmin()
            ),
            'age' => $this->when(
                $request->user()?->id == $this->id || $request->user()?->isAdmin(),
                $this->age
            ),
            // TODO show authorizations when admin or owner
            // TODO show settings
        ];
    }
}
