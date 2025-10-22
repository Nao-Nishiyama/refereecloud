<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    public function nominations()
    {
        return $this->hasMany(Nomination::class);
    }

    public function competitionOfficial()
    {
        return $this->hasMany(CompetitionOfficial::class);
    }
}
