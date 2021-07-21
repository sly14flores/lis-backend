<?php

namespace App\Http\Resources\CommitteeReport;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

class CommitteeReportListResource extends JsonResource
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
                'id' => $for_referral['id'],
                'subject' => $for_referral['subject'],
                'committee_meeting' => $for_referral->committee_meetings->pluck('meeting_date'),
                'committee_hearing' => $for_referral->committee_hearings->pluck('hearing_date'),
                'public_hearing' => $for_referral->public_hearings->pluck('hearing_date')
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
            'for_referrals' => $communications,
            'date_received' => $this->date_received,
            'agenda_date' => $this->agenda_date,
            'remarks' => $this->remarks,
            // 'meeting_date' => $this->for_referral
            'date_created' => $this->created_at,
        ];
    }
}