<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    public function referees()
    {
        return $this->hasMany(Referee::class);
    }
}
