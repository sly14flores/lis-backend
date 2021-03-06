<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Committee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];    

    /**
     * @param $value
     * @return false|string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('F j, Y h:i A');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class)->withPivot('chairman', 'vice_chairman', 'member');
    }

    public function for_referrals()
    {
        return $this->belongsToMany(ForReferral::class)->withPivot('lead_committee', 'joint_committee');
    }
}