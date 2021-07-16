<?php

namespace App\Http\Resources\CommitteeMeeting;

use Illuminate\Http\Resources\Json\JsonResource;

class CommitteeMeetingResource extends JsonResource
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
            'for_referral_id' => $this->for_referral_id,
            'subject' => $this->for_referrals->subject,
            'meeting_date' => $this->meeting_date,
        ];
    }
}
