<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    # A post has one user(inverse)
    # To get the owner of a post
    public function user(){
        return $this->belongsTo(User::class)->withTrashed();
    }

    # To get the categories of a post but only IDs
    public function categoryPost(){
        return $this->hasMany(CategoryPost::class);
    }

    # To get all the comments of a post
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    # Post has many likes
    # To get all the likes of a post
    public function likes()
    {
        return $this->hasMany(Like::class);    
    }

    # Returns TRUE if the Auth user already liked the post
    public function isLiked()
    {
        return $this->likes()->where('user_id', Auth::user()->id)->exists();
    }
}
