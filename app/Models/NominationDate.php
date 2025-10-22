<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominationDate extends Model
{
    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
