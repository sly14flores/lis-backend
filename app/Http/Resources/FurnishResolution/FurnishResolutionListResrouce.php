<?php

namespace App\Http\Resources\FurnishResolution;

use Illuminate\Http\Resources\Json\JsonResource;

class FurnishResolutionListResrouce extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'date_created' => $this->created_at
        ];
    }
}
