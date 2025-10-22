<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsTo, BelongsToMany
};
use Illuminate\Database\Eloquent\SoftDeletes;

class Nomination extends Model
{
    use SoftDeletes;

    // テーブル名（デフォルトで 'nominations' なら省略可）
    protected $table = 'nominations';

    // 一括代入
    protected $fillable = [
        'competition_id',
        'day_id',
        'official_id',
        'capacity',
        'filters_json',
        'note',
    ];

    // キャスト
    protected $casts = [
        'capacity'     => 'integer',
        'filters_json' => 'array',   // {"license_ids":[...], "category_ids":[...]}
    ];

    /* ========== リレーション ========== */

    /** 親: 大会 */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /** 親: 大会日（dateはCompetitionDay側で 'date' => 'date' キャスト推奨） */
    public function day(): BelongsTo
    {
        return $this->belongsTo(CompetitionDay::class, 'day_id');
    }

    /** 親: 役職 */
    public function official(): BelongsTo
    {
        return $this->belongsTo(Official::class);
    }

    /** 多対多: 実際に紐づいているレフェリー（招待/応募/割当等） */
    public function referees(): BelongsToMany
    {
        return $this->belongsToMany(Referee::class, 'nomination_referee')
            ->withPivot(['status', 'meta_json'])
            ->withTimestamps();
    }

    /* ========== 便利アクセサ / 補助 ========== */

    /** このセルの date（Carbon）を安全に取得 */
    public function getDateAttribute()
    {
        // CompetitionDay 側: protected $casts = ['date' => 'date'];
        return optional($this->day)->date;
    }

    /** フィルタ配列を正規化（license_ids/category_ids を int 化） */
    public function normalizedFilters(): array
    {
        $f = $this->filters_json ?? [];
        $toIntArr = fn($a) => array_values(array_filter(array_map('intval', (array)($a ?? []))));
        return [
            'license_ids'  => $toIntArr($f['license_ids']  ?? []),
            'category_ids' => $toIntArr($f['category_ids'] ?? []),
        ];
    }

    /* ========== スコープ ========== */

    /** 大会で絞る */
    public function scopeForCompetition($q, int $competitionId)
    {
        return $q->where('competition_id', $competitionId);
    }

    /** 役職で絞る */
    public function scopeForOfficial($q, int $officialId)
    {
        return $q->where('official_id', $officialId);
    }

    /** 日付範囲で絞る（JOIN前提） */
    public function scopeBetweenDates($q, $fromDate, $toDate)
    {
        // CompetitionDay に 'date' カラムがある前提
        return $q->whereHas('day', function ($w) use ($fromDate, $toDate) {
            $w->whereBetween('date', [
                \Illuminate\Support\Carbon::parse($fromDate)->toDateString(),
                \Illuminate\Support\Carbon::parse($toDate)->toDateString(),
            ]);
        });
    }

    /** ログイン中レフェリーが招待対象（license OR category）かを判定（動的判定） */
    public function isEligibleReferee(?Referee $referee): bool
    {
        if (!$referee) return false;
        $f = $this->normalizedFilters();
        return $referee->isEligibleFor($f['license_ids'], $f['category_ids']); // Referee側に前述のメソッドを実装
    }

    /* ========== よくあるユースケース例（参考） ========== */

    /**
     * 例: セル（official×day）を upsert する時に使うデータ行を作る
     */
    public static function makeUpsertRow(int $competitionId, int $dayId, int $officialId, array $filters = [], ?int $capacity = null): array
    {
        return [
            'competition_id' => $competitionId,
            'day_id'         => $dayId,
            'official_id'    => $officialId,
            'capacity'       => $capacity,
            'filters_json'   => json_encode([
                'license_ids'  => array_values(array_filter(array_map('intval', $filters['license_ids']  ?? []))),
                'category_ids' => array_values(array_filter(array_map('intval', $filters['category_ids'] ?? []))),
            ]),
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    public function capacities() {
        return $this->hasMany(NominationCapacity::class);
    }
    // 便利: 団体ID→capacity の連想配列に
    public function capacityMap(): array {
        return $this->capacities->pluck('capacity','organization_id')->map(fn($v)=>(int)$v)->all();
    }

    protected static function booted(): void
    {
        static::deleting(function (Nomination $n) {
            $n->capacities()->get()->each->delete(); // hasMany NominationCapacity
        });
        static::restoring(function (Nomination $n) {
            $n->capacities()->onlyTrashed()->get()->each->restore();
        });
    }

}
