<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'applications';
    protected $fillable = ['user_id','nomination_id','status'];

    public const S_APPLIED = 'applied';
    public const S_UNDER_REVIEW = 'under_review';
    public const S_WAITLISTED = 'waitlisted';
    public const S_INVITED = 'invited';
    public const S_ACCEPTED = 'accepted';
    public const S_DECLINED = 'declined';
    public const S_ASSIGNED = 'assigned';
    public const S_WITHDRAWN = 'withdrawn';
    public const S_REJECTED = 'rejected';
    public const S_EXPIRED = 'expired';
    public const S_CANCELED_BY_ORG = 'canceled_by_org';

    public function user()       { return $this->belongsTo(User::class); }
    public function competition(){ return $this->belongsTo(Competition::class); }
    public function nomination() { return $this->belongsTo(Nomination::class); }
}
