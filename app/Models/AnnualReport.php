<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualReport extends Model
{
    protected $table = 'annual_reports';
    protected $fillable = 
        ['user_id',
        'year',
        'first_ref_block',
        'second_ref_block',
        'first_ref',
        'second_ref',
        'scorer',
        'assistant_scorer',
        'linejudge',
        'training'];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
