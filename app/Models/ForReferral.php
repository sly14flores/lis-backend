<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class ForReferral extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject',
        'date_received',
        'category_id',
        'origin_id',
        'agenda_date',
        'archive',
        'urgent',
        'file'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'archive' => 'boolean',
        'urgent'=> 'boolean',
    ];


    /**
     * @param $value
     * @return false|string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('F j, Y h:i A');
    }

    public function category()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsTo(Category::class);
    }
    
    public function origin()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsTo(Origin::class);
    }

    public function committees()
    {
        return $this->belongsToMany(Committee::class)->withPivot('lead_committee', 'joint_committee');
    }

    public function endorsements()
    {
        return $this->belongsToMany(Endorsement::class);
    }

    public function committee_reports()
    {
        return $this->belongsToMany(CommitteeReport::class)->withPivot('remarks');
    }

    public function second_reading()
    {
        return $this->hasOne(SecondReading::class);
    }

    public function third_reading()
    {
        return $this->hasOne(ThirdReading::class);
    }

    public function resolutions()
    {
        return $this->belongsToMany(Resolution::class);
    }

    public function ordinances()
    {
        return $this->hasOne(Ordinance::class);
    }

    public function appropriations()
    {
        return $this->hasOne(Appropriation::class);
    }

    public function committee_meetings()
    {
        return $this->hasMany(CommitteeMeeting::class);
    }

    public function committee_hearings()
    {
        return $this->hasMany(CommitteeHearing::class);
    }

    public function public_hearings()
    {
        return $this->hasMany(PublicHearing::class);
    }

    public function comm_status()
    {
        return $this->hasOne(CommunicationStatus::class);
    }

}
