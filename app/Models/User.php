<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UserCompetitionRole;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    const ADMIN_ROLE_ID = 1;
    const COMMITTEE_ROLE_ID = 2;
    const CHIEF_ROLE_ID = 3;
    const USER_ROLE_ID  = 4;

    public function isAdmin()     { return $this->role_id === self::ADMIN_ROLE_ID; }
    public function isCommittee() { return $this->role_id === self::COMMITTEE_ROLE_ID; }
    public function isChief()     { return $this->role_id === self::CHIEF_ROLE_ID; }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            // すでにrole_idが明示指定されていれば尊重
            if (!is_null($user->role_id)) return;

            // まだ1件もユーザーがいなければ＝最初の登録 → admin
            $isFirst = !static::query()->exists();
            $user->role_id = $isFirst ? self::ADMIN_ROLE_ID : self::USER_ROLE_ID;
        });
    }
    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $fillable = [
        'surname_kanji', 'name_kanji',
        'surname', 'name',
        'surname_kana','name_kana',
        'registration_number',
        'license_id',
        'prefecture_id',
        'organization_id',
        'birth_date',
        'email',
        'password',
        'position_id',
        'mrs_member_id',
        'remarks',
        'role_id'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }
 
    // 不足情報を入力
    public function getIsProfileCompleteAttribute(): bool
    {
        return filled($this->prefecture_id)
            && filled($this->organization_id)
            && filled($this->license_id)
            && filled($this->birth_date);
    }

    public function isNominated()
    {
        return $this->hasMany(Nomination::class);
    }

    public function referee()
    {
        return $this->hasOne(Referee::class);
    }

    public function reports() 
    {
        return $this->hasMany(AnnualReport::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function applications() {
        return $this->hasMany(Application::class);
    }

}
