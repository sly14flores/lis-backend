<?php

namespace App\Http\Resources\Appropriation;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

class AppropriationResource extends JsonResource
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
            'appropriation_no' => $this->appropriation_no,
            'for_referral_id' => $this->for_referral_id,
            'title' => $this->title,
            'date_passed' => $this->date_passed,
            'file' => $this->file,
            'view' => env('STORAGE_URL').Storage::url($this->file),
        ];
    }
}
