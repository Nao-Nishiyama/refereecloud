<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingFile extends Model
{
    protected $fillable = ['training_id','path','original_name','uploaded_by'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
