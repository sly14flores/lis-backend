<?php

namespace App\Http\Resources\CommunicationStatus;

use Illuminate\Http\Resources\Json\JsonResource;

class CommunicationStatusListResource extends JsonResource
{
   /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $report = (is_null($this->for_referrals->committee_reports))?null:$this->for_referrals->committee_reports;
        $resolution = (is_null($this->for_referrals->resolutions->first()))?null:$this->for_referrals->resolutions->first();
        return [
            'comm_status_id' => $this->id,
            'for_referral_id' => $this->for_referral_id,
            'subject' => (is_null($this->for_referrals))?null:$this->for_referrals->subject,
            'agenda_date' => (is_null($this->for_referrals))?null:$this->for_referrals->agenda_date,
            'committee_report' => $report,
            // 'date_received' => (is_null($this->for_referrals))?null:$this->for_referrals->date_received,
            'resolution_no' => (is_null($resolution))?null:$resolution->resolution_no,
            'resolution_subject' => (is_null($resolution))?null:$resolution->subject,
            'resolution_passed' => (is_null($resolution))?null:$resolution->date_passed,
            'ordinance_id' => (is_null($this->for_referrals->ordinances))?null:$this->for_referrals->ordinances->id,
            'ordinance_no' => (is_null($this->for_referrals->ordinances))?null:$this->for_referrals->ordinances->ordinance_no,
            'title' => (is_null($this->for_referrals->ordinances))?null:$this->for_referrals->ordinances->title,
            'date_created' => $this->created_at,
        ];
    }
}
