<?php

namespace App\Http\Resources\PublicHearing;

use Illuminate\Http\Resources\Json\JsonResource;

class PublicHearingResource extends JsonResource
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
            'hearing_date' => $this->hearing_date,
        ];
    }
}
