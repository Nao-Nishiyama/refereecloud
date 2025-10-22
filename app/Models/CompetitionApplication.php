<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitionApplication extends Model
{
    protected $table = 'competition_applications';
    protected $fillable = ['competition_id','nomination_id','referee_id','status','meta_json'];
    protected $casts = ['meta_json' => 'array'];

    public function competition() { return $this->belongsTo(Competition::class); }
    public function nomination()  { return $this->belongsTo(Nomination::class); }
    public function referee()     { return $this->belongsTo(Referee::class); }
}
