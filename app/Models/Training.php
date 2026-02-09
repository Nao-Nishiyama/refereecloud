<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'title',
        'event_date',
        'venue',
        'prefecture_id',
        'organization_id',
        'summary',
        'body',
        'deadline',
        'is_published',
        'created_by',
        'pdf_path',
        'link_url',
        'link_label',
    ];

    protected $casts = [
        'event_date' => 'date',
        'deadline'   => 'date',
        'is_published' => 'boolean',
    ];

    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function files()
    {
        return $this->hasMany(TrainingFile::class)->latest();
    }

    public function latestFile()
    {
        return $this->hasOne(TrainingFile::class)->latestOfMany();
    }

}
