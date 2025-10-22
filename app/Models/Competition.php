<?php

namespace App\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competition extends Model
{

    use SoftDeletes;
    
    protected $fillable = [
        'name', 'city', 'venue', 'start_day', 'end_day',
        'application_deadline', 'type_id', 'organizer_message', 'admin_private_note'
    ];

    public function competitionLicense()
    {
        return $this->hasMany(CompetitionLicense::class);
    }

    public function competitionCategory()
    {
        return $this->hasMany(CompetitionCategory::class);
    }

    public function competitionOfficial()
    {
        return $this->hasMany(CompetitionOfficial::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function nominations()
    {
        return $this->hasMany(Nomination::class);
    }

    public function officials()
    {
        return $this->belongsToMany(Official::class, 'competition_official', 'competition_id', 'official_id');
    }

    public function licenses()
    {
        return $this->belongsToMany(License::class, 'competition_license', 'competition_id', 'license_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'competition_category', 'competition_id', 'category_id');
    }

    public function days()
    {
        return $this->hasMany(CompetitionDay::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Competition $c) {
            if ($c->isForceDeleting()) {
                // 物理削除時
                $c->nominations()->withTrashed()->get()->each->forceDelete();
                $c->days()->withTrashed()->get()->each->forceDelete();
            } else {
                // 論理削除時
                $c->nominations()->get()->each->delete();
                $c->days()->get()->each->delete();
            }
        });

        static::restoring(function (Competition $c) {
            // 復元時に子も復元
            $c->nominations()->onlyTrashed()->get()->each->restore();
            $c->days()->onlyTrashed()->get()->each->restore();
        });
    }

    public function applications()
    {
        return $this->hasManyThrough(
            \App\Models\Application::class,
            \App\Models\Nomination::class,
            'competition_id',   // nominations.competition_id
            'nomination_id',    // applications.nomination_id
            'id',               // competitions.id
            'id'                // nominations.id
        );
    }
}
