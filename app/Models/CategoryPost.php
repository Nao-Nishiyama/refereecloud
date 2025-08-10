<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryPost extends Model
{
    use HasFactory;

    protected $table = 'category_post';
    protected $fillable = ['category_id', 'post_id'];
    public $timestamps = false; // hey laravel we won't use timestamps

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    
}
