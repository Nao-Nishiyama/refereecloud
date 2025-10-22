<?php

namespace App\Models;

use App\Models\CompetitionUserRole;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{

    public function competitions()
    {
        return $this->belongsToMany(Competition::class, 'competition_role')
                    ->withPivot('date_name')
                    ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
