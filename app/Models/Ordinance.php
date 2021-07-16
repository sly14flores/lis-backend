<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Ordinance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'for_referral_id',
        'ordinance_no',
        'title',
        'amending',
        'date_passed',
        'date_signed',
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
        return $this->belongsToMany(Bokal::class)->withPivot('author', 'co_author');
    }

    public function agencies()
    {
        return $this->belongsToMany(Agency::class);
    }

    public function for_referral()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsTo(ForReferral::class);
    }

    public function publication()
    {
        return $this->hasOne(Publication::class,'ordinance_id','id');
    }

    public function origins()
    {
        return $this->belongsToMany(Origin::class)->withPivot('date_furnished');
    }
}
