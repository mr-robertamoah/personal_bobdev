<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
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
            'to' => $this->when(
                $this->isToCompany(),
                new CompanyResource($this->to),
                new UserResource($this->to)
            ),
            'from' => $this->when(
                $this->isFromCompany(),
                new CompanyResource($this->from),
                new UserResource($this->from)
            ),
            'for' => $this->when(
                $this->isForCompany(),
                new CompanyResource($this->for),
                new ProjectResource($this->for)
            ),
            'purpose' => $this->purpose,
            'state' => $this->state,
            'createAt' => $this->created_at
        ];
    }
}
