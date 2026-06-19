<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    public function referees()
    {
        return $this->hasMany(Referee::class);
    }

    public function refereeLicenseHistories()
    {
        return $this->hasMany(\App\Models\RefereeLicenseHistory::class);
    }

    public function competitionLicense()
    {
        return $this->hasMany(CompetitionLicense::class);
    }

    public function nomination()
    {
        return $this->hasMany(Nomination::class);
    }
}
