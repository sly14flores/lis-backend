<?php

namespace App\Http\Resources\CommunicationStatus;

use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationStatusResource extends JsonResource
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
            'comm_status_id' => $this->id,
            'for_referral_id' => $this->for_referral_id,
            'subject' => (is_null($this->for_referrals))?null:$this->for_referrals->subject,
            'agenda_date' => (is_null($this->for_referrals))?null:$this->for_referrals->agenda_date,
            'date_received' => (is_null($this->for_referrals))?null:$this->for_referrals->date_received,
            'resolution_no' => (is_null($this->for_referrals->resolutions))?null:$this->for_referrals->resolutions->id,
            'ordianance_no' => (is_null($this->for_referrals->ordinances))?null:$this->for_referrals->ordinances->id,
            'title' => (is_null($this->for_referrals->ordinances))?null:$this->for_referrals->ordinances->title,
        ];
    }
}
