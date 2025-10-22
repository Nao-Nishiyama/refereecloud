<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NominationCapacity extends Model
{
    use SoftDeletes;
    
    protected $fillable = ['nomination_id','organization_id','capacity', 'deleted_at'];
    
    public function nomination(){
        return $this->belongsTo(Nomination::class); 
    }

    public function organization(){
        return $this->belongsTo(Organization::class); 
    }
}