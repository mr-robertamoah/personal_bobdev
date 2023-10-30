<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        ds($this->resource);
        return [
            'id' => $this->id,
            'firstName' => $this->first_name,
            'surname' => $this->surname,
            'otherNames' => $this->other_names,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'gender' => strtolower($this->gender),
            "isAdult" => $this->isAdult(),
            "isAdmin" => $this->when(
                $this->isAdmin() || $this->is($request->user()),
                $this->isAdmin()
            ),
            "isFacilitator" => $this->isFacilitator(),
            "isLearner" => $this->isLearner(),
            "isParent" => $this->isParent(),
            "isWard" => $this->isWard(),
            "isSponsor" => $this->isSponsor(),
        ];
    }
}
