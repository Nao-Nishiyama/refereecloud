<?php

namespace App\Models;

use App\Models\RefereeLicenseHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Referee extends Model
{

    protected $fillable = [
    'user_id','surname','name','surname_kanji','name_kanji',
    'surname_kana','name_kana','registration_number','prefecture_id',
    'organization_id','license_id','birth_date', 'gender','mrs_member_id','remarks', 'created_at', 'updated_at', 'deleted_at'
    ];

    use SoftDeletes;
    
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    
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

    public function licenseHistories()
    {
        return $this->hasMany(RefereeLicenseHistory::class);
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

        // 両方空なら「制限なし（全員OK）」にする
        if (empty($licenseIds) && empty($categoryIds)) {
            return $q;
        }

        return $q->where(function ($qq) use ($licenseIds, $categoryIds) {

            // license 条件
            if (!empty($licenseIds)) {
                $qq->whereIn('license_id', $licenseIds);
            }

            // category 条件（多対多）
            if (!empty($categoryIds)) {
                // license条件が既にあるなら OR でつなぐ、無いなら whereHas
                $method = !empty($licenseIds) ? 'orWhereHas' : 'whereHas';

                $qq->{$method}('categories', function ($cq) use ($categoryIds) {
                    // categories テーブルの主キーが id の想定
                    $cq->whereIn('categories.id', $categoryIds);
                });
            }
        });
    }

    public function approval()
    {
        return $this->hasOne(RefereeApproval::class);
    }

    protected function genderName(): Attribute
    {
        return Attribute::make(
            get: fn () => match ((int) $this->gender) {
                1 => '男',
                2 => '女',
                default => '',
            }
        );
    }

    public function getCurrentLicenseYearAttribute()
    {
        $history = $this->licenseHistories
            ->where('license_id', $this->license_id)
            ->sortByDesc('acquired_year')
            ->first();

        return $history?->acquired_year;
    }

    public function getCurrentLicenseYearShortAttribute()
    {
        $year = $this->current_license_year;

        return $year
            ? substr((string)$year, -2)
            : '';
    }
    
}
