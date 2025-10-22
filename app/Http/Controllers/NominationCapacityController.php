<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Nomination;
use App\Models\Competition;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\NominationCapacity;
use Illuminate\Support\Facades\DB;

class NominationCapacityController extends Controller
{
    private $competition;

    private function fiscalRange(int $year): array
    {
        $from = Carbon::create($year, 4, 1)->startOfDay();
        $to   = Carbon::create($year + 1, 3, 31)->endOfDay();
        return [$from, $to];
    }

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    /** 一覧（閲覧用） */
    public function show(Request $request)
    {
        // 今年度をデフォルト
        $today = now('Asia/Tokyo');
        $defaultYear = $today->month >= 4 ? $today->year : $today->year - 1;

        $year = (int)($request->query('year', $defaultYear));
        [$from, $to] = $this->fiscalRange($year);

        // 審判員が1人以上いる団体のみ
        $organizations = Organization::withCount('referees')
            ->having('referees_count','>',0)
            ->orderBy('id')
            ->get(['id','short_name']);

        // 年度内に day を持つ大会のみ取得。大会の中の nominations も年度内だけに限定して with。
        $competitions = Competition::query()
            ->whereHas('nominations.day', fn($q) => $q->whereBetween('date', [$from->toDateString(), $to->toDateString()]))
            ->with([
                'nominations' => function ($q) use ($from,$to) {
                    $q->whereHas('day', fn($dq) => $dq->whereBetween('date', [$from->toDateString(), $to->toDateString()]))
                      ->with(['day','official']);
                },
            ])
            ->orderBy('start_day')
            ->get();

        // 既存 capacity を [nomination_id][organization_id] => capacity に整形
        $nominationIds = $competitions->flatMap->nominations->pluck('id')->unique()->values();
        $caps = [];
        if ($nominationIds->isNotEmpty()) {
            $rows = NominationCapacity::whereIn('nomination_id', $nominationIds)->get();
            foreach ($rows as $r) {
                $caps[$r->nomination_id][$r->organization_id] = (int) $r->capacity;
            }
        }

        return view('admin.nominations.show', compact(
            'year','from','to','competitions','organizations','caps'
        ));
    }

    /** 編集（入力用） */
    public function edit(Request $request)
    {
        $today = now('Asia/Tokyo');
        $defaultYear = $today->month >= 4 ? $today->year : $today->year - 1;

        $year = (int)($request->query('year', $defaultYear));
        [$from, $to] = $this->fiscalRange($year);

        $organizations = Organization::withCount('referees')
            ->having('referees_count','>',0)
            ->orderBy('id')
            ->get(['id','short_name']);

        $competitions = Competition::query()
            ->whereHas('nominations.day', fn($q) => $q->whereBetween('date', [$from->toDateString(), $to->toDateString()]))
            ->with([
                'nominations' => function ($q) use ($from,$to) {
                    $q->whereHas('day', fn($dq) => $dq->whereBetween('date', [$from->toDateString(), $to->toDateString()]))
                      ->with(['day','official']);
                },
            ])
            ->orderBy('start_day')
            ->get();

        $nominationIds = $competitions->flatMap->nominations->pluck('id')->unique()->values();
        $caps = [];
        if ($nominationIds->isNotEmpty()) {
            $rows = NominationCapacity::whereIn('nomination_id', $nominationIds)->get();
            foreach ($rows as $r) {
                $caps[$r->nomination_id][$r->organization_id] = (int) $r->capacity;
            }
        }

        return view('admin.nominations.capacities_edit', compact(
            'year','from','to','competitions','organizations','caps'
        ));
    }

    /** 一括保存（年度パラメータを持ち回す） */
    public function bulkUpdate(Request $request)
    {
        $year = (int)$request->query('year', now('Asia/Tokyo')->month >= 4 ? now('Asia/Tokyo')->year : now('Asia/Tokyo')->year - 1);

        $data = $request->validate([
            'capacity'      => 'array',
            'capacity.*'    => 'array',
            'capacity.*.*'  => 'nullable|integer|min:0',
        ]);
        $incoming = $data['capacity'] ?? [];

        DB::transaction(function () use ($incoming) {
            foreach ($incoming as $nominationId => $byOrg) {
                foreach ($byOrg as $orgId => $cap) {
                    $cap = ($cap === '' ? null : $cap);
                    if ($cap === null || (int)$cap === 0) {
                        \App\Models\NominationCapacity::where('nomination_id', $nominationId)
                            ->where('organization_id', $orgId)->delete();
                    } else {
                        \App\Models\NominationCapacity::updateOrCreate(
                            ['nomination_id' => $nominationId, 'organization_id' => $orgId],
                            ['capacity' => (int)$cap]
                        );
                    }
                }
            }
        });

        // 年度を維持して戻る
        return redirect()->route('admin.nominations.capacities.edit', ['year' => $year])
            ->with('status','団体別の割当を保存しました。');
    }
}