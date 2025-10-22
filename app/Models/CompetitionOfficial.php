<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitionOfficial extends Model
{
    use HasFactory;

    protected $table = 'competition_official';
    protected $fillable = ['competition_id', 'official_id'];

    public function official()
    {
        return $this->belongsTo(Official::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

}
