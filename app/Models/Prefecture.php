<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prefecture extends Model
{
    public function referees()
    {
        return $this->hasMany(Referee::class);
    }
}
