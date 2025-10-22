<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominationReferee extends Model
{
    protected $table = 'nomination_referee';
    protected $fillable = ['nomination_id','referee_id','status','meta_json'];

    protected $casts = ['meta_json' => 'array'];

}
