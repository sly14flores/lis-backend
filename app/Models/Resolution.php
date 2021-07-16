<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Resolution extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'resolution_no',
        'subject',
        'bokal_id',
        'date_passed',
        'archive',
        'file',
        
    ];
    
    protected $casts = [
        'archive' => 'boolean'
    ];

    /**
     * @param $value
     * @return false|string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('F j, Y h:i A');
    }

    public function bokals()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsTo(Bokal::class,'bokal_id','id');
    }

    public function for_referral()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsToMany(ForReferral::class);
    }

    public function origins()
    {
        return $this->belongsToMany(Origin::class)->withPivot('date_furnished');
    }
}
