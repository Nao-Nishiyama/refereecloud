<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = ['category_id'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function nominations()
    {
        return $this->hasMany(Nomination::class);
    }
}
