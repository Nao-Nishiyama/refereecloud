<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    public $timestamps = false;

    # To get the info like name of a follower
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id')->withTrashed();
    }

    # One to Many (inverse)
    # To get the info like name of the following user
    public function following()
    {
        return $this->belongsTo(User::class, 'following_id')->withTrashed();
    }
}
