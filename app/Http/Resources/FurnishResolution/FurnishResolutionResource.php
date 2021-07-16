<?php

namespace App\Http\Resources\FurnishResolution;

use Illuminate\Http\Resources\Json\JsonResource;

class FurnishResolutionResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
        ];
    }
}
