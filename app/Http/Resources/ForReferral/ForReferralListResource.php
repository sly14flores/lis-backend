<?php

namespace App\Http\Resources\ForReferral;

use Illuminate\Http\Resources\Json\JsonResource;

use Illuminate\Support\Facades\Storage;

class ForReferralListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $committees = $this->committees()->get(['committees.id', 'committees.name']); # All
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
            'id' => $this->id,
            'subject' => $this->subject,
            'date_received' => $this->date_received,
            'category' => (is_null($this->category))?null:$this->category,
            'origin' => (is_null($this->origin))?null:$this->origin,
            'agenda_date' => $this->agenda_date,
            'lead_committee' => (is_null($lead_committee))?null:$lead_committee,
            'joint_committees' => (is_null($joint_committees))?null:$joint_committees,
            'date_created' => $this->created_at
        ];
    }
}
