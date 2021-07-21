<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class CommitteeReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date_received',
        'agenda_date',
        'archive',
        'file'
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

    public function for_referral()
    {
        // return $this->belongsTo(Group::class,'group_id','id');
        return $this->belongsToMany(ForReferral::class)->withPivot('remarks');
    }
}