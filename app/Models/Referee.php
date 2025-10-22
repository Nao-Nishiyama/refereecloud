<?php

namespace App\Models;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referee extends Model
{

    protected $fillable = [
    'user_id','surname','name','surname_kanji','name_kanji',
    'surname_kana','name_kana','registration_number','prefecture_id',
    'organization_id','license_id','birth_date','mrs_member_id','remarks', 
    'created_at', 'updated_at', 'deleted_at'
    ];

    use SoftDeletes;
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prefecture()
    {
        return $this->belongsTo(Prefecture::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function license()
    {
        return $this->belongsTo(License::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function nominations()
    {
        return $this->hasMany(Nomination::class);
    }

    public function sortByLicense($id)
    {
        return $this->referee->where('license_id', $id)->get()->orderBy('organization_id');
    }

    /**
     * license OR category のいずれか一致（単一FK + 多対多の両対応）
     */
    public function scopeEligibleFor($q, array $licenseIds = [], array $categoryIds = [])
    {
        $licenseIds  = array_values(array_filter(array_map('intval', $licenseIds)));
        $categoryIds = array_values(array_filter(array_map('intval', $categoryIds)));

        return $q->when(!empty($licenseIds),  fn($qq)=>$qq->whereIn('license_id', $licenseIds))
                ->when(!empty($categoryIds), function ($qq) use ($categoryIds) {
                    $qq->where(function ($w) use ($categoryIds) {
                        // referees.category_id（FK）がある場合
                        if (Schema::hasColumn('referees','category_id')) {
                            $w->orWhereIn('category_id', $categoryIds);
                        }
                        // 多対多
                        $w->orWhereHas('categories', fn($r)=>$r->whereIn('categories.id', $categoryIds));
                    });
                });
    }

    public function approval()
    {
        return $this->hasOne(RefereeApproval::class);
    }
}
