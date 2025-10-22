<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefereeApproval extends Model
{
    protected $fillable = ['referee_id','year','approved','suspended'];
    protected $casts = [
        'year'      => 'integer',
        'approved'  => 'boolean',
        'suspended' => 'boolean',
    ];

    public function referee()
    {
        return $this->belongsTo(Referee::class);
    }

}
