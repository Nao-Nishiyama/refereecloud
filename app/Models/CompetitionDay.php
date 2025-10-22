<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionDay extends Model
{
    use SoftDeletes;
    
    protected $casts = [
        'date' => 'date',  // ← これで $day->date は常に Carbon
    ];
}
