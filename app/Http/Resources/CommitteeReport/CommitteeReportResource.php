<?php

namespace App\Http\Resources\CommitteeReport;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

class CommitteeReportResource extends JsonResource
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
        $remarks = $this->for_referral()->get(['remarks']);
        $remarks = $remarks->map(function ($remark) {
            return [
                $remark['remarks']
            ];
        });

        return [
            'id' => $this->id,
            'for_referrals' => $communications,
            'date_received' => $this->date_received,
            'agenda_date' => $this->agenda_date,
            'lead_committee' => (is_null($committees['lead_committee']))?'N/A':$committees['lead_committee']['name'],
            'joint_committees' => (is_null($committees['joint_committees']))?'N/A':$committees['joint_committees'],
            'remarks' => $remarks,
            'meeting_date' => $this->meeting_date,
            'file' => $this->file,
            'view' => env('STORAGE_URL').Storage::url($this->file),
        ];
    }
}
