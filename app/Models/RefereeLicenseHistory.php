<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefereeLicenseHistory extends Model
{
    protected $fillable = [
        'referee_id',
        'license_id',
        'acquired_year',
        'note',
    ];

    public function referee()
    {
        return $this->belongsTo(Referee::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }
}