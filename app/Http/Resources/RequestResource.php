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
                new MiniCompanyResource($this->to),
                new MiniUserResource($this->to)
            ),
            'from' => $this->when(
                $this->isFromCompany(),
                new MiniCompanyResource($this->from),
                new MiniUserResource($this->from)
            ),
            'for' => $this->when(
                $this->isForCompany(),
                new MiniCompanyResource($this->for),
                $this->isForProject() ? 
                    new MiniProjectResource($this->for) : null
            ),
            'purpose' => $this->purpose,
            'type' => strtolower($this->type),
            'state' => strtolower($this->state),
            'createAt' => $this->created_at->diffForHumans(),
        ];
    }
}
