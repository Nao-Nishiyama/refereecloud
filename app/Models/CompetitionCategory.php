<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompetitionCategory extends Model
{
    use HasFactory;

    protected $table = 'competition_category';
    protected $fillable = ['competition_id', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
