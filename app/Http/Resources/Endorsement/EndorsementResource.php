<?php

namespace App\Http\Resources\Endorsement;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

class EndorsementResource extends JsonResource
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
        $for_referral = $for_referrals->map(function ($for_referral) {
            return[
                'id' => $for_referral['id'],
                'subject' => $for_referral['subject']
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
            'for_referrals' => $for_referral,
            'date_endorsed' => $this->date_endorsed,
            'lead_committee' => (is_null($committees['lead_committee']))?null:$committees['lead_committee']['name'],
            'joint_committees' => (is_null($committees['joint_committees']))?null:$committees['joint_committees'],
            'file' => $this->file,
            'view' => env('STORAGE_URL').Storage::url($this->file),
        ];
    }
}
