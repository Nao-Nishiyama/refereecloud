<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitionLicense extends Model
{
    use HasFactory;

    protected $table = 'competition_license';
    protected $fillable = ['competition_id', 'license_id'];

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

}
