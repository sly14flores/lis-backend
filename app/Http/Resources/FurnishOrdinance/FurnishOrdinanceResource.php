<?php

namespace App\Http\Resources\FurnishOrdinance;

use Illuminate\Http\Resources\Json\JsonResource;

class FurnishOrdinanceResource extends JsonResource
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
