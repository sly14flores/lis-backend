<?php

namespace App\Http\Resources\Resolution;

use Illuminate\Http\Resources\Json\JsonResource;

class ResolutionListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $for_referrals = $this->for_referral; # All
        $communications = $for_referrals->map(function ($for_referral) {
            return[
                'id' => $for_referral['id']
            ];
        });
        $committees = $for_referrals->map(function ($for_referral) {
            $committees = $for_referral->committees;
            $lead_committee = $committees->filter(function ($committee) {
                return $committee->pivot->lead_committee === 1;
            })->values()->first();
            $joint_committees = $committees->filter(function ($committee) {
                return $committee->pivot->joint_committee === 1;
            })->values();
            $joint_committees = $joint_committees->map(function ($joint_committee) {
                return [
                    'id' => $joint_committee['id'],
                    'name' => $joint_committee['name'],
                ];
            });
            return [
                'lead_committee' => $lead_committee,
                'joint_committees' => $joint_committees
            ];
        })->first();

        return [
            'id' => $this->id,
            'resolution_no' => $this->resolution_no,
            'subject' => $this->subject,
            'for_referrals' => $communications,
            'author' => "Hon. " . $this->bokals->first_name. " " . $this->bokals->middle_name . " " . $this->bokals->last_name,
            // 'author' => "Hon. ".$this->bokals->first_name." ".$this->bokals->middle_name." ".$this->bokals->last_name,
            'date_passed' => $this->date_passed,
            'date_created' => $this->created_at
        ];
    }
}
