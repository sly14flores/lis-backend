<?php

namespace App\Http\Resources\ThirdReading;

use Illuminate\Http\Resources\Json\JsonResource;

class ThirdReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
<<<<<<< Updated upstream
        return parent::toArray($request);
=======
        return[
            'id' => $this->id,
            'subject' => (is_null($this->for_referral))?null:$this->for_referral->subject,
            'date_received' => $this->date_received,
            'agenda_date' => $this->agenda_date,
            'file' => $this->file,
        ];
>>>>>>> Stashed changes
    }
}
