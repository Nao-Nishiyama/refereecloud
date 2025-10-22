<?php

namespace App\Http\Controllers;

use App\Models\Referee;
use App\Models\Nomination;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChiefAssignController extends Controller
{
    private $competition;

    public function __construct(Competition $competition, )
    {
        $this->competition = $competition;
    }

    /** ログイン chief の所属団体ID（Referee 経由） */
    private function chiefOrgId(Request $request): int
    {
        $ref = Referee::where('user_id', $request->user()->id)->first();
        return (int)($ref->organization_id ?? 0);
    }

    /** 候補一覧（filters_json に合致 ＆ 自団体所属） */
    public function candidates(Request $request, Nomination $nomination)
    {
        $chiefOrgId = $this->chiefOrgId($request);
        if ($chiefOrgId <= 0) abort(403);

        $f = (array)($nomination->filters_json ?? []);
        $licenseIds  = array_map('intval', (array)($f['license_ids']  ?? []));
        $categoryIds = array_map('intval', (array)($f['category_ids'] ?? []));

        // Referee::scopeEligibleFor は「license OR category 一致」のスコープ（前に出したやつ）
        $candidates = Referee::query()
            ->eligibleFor($licenseIds, $categoryIds)
            ->where('organization_id', $chiefOrgId)
            ->with(['organization:id,name','categories:id,name']) // 多対多があるなら
            ->orderBy('id')
            ->get();

        $assigned = $nomination->referees()
            ->wherePivot('status','assigned')
            ->pluck('referees.id')->flip()->all();

        return view('chief.candidates', compact('nomination','candidates','assigned'));
    }

    public function assign(Request $request, Nomination $nomination)
    {
        $data = $request->validate([
            'referee_id' => ['required','integer','exists:referees,id'],
        ]);

        // ここまでに: chiefの所属チェック / filters_jsonの対象チェック は済ませている前提
        $refereeId = (int)$data['referee_id'];
        $userId    = (int)$request->user()->id;

        return DB::transaction(function () use ($nomination, $refereeId, $userId) {

            // 1) nomination をロックして取得（行ロック）
            /** @var \App\Models\Nomination $locked */
            $locked = \App\Models\Nomination::query()
                ->whereKey($nomination->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2) すでにこのレフェリーが assigned 済みなら早期リターン
            $alreadyAssigned = $locked->referees()
                ->wherePivot('status','assigned')
                ->where('referees.id', $refereeId)
                ->exists();
            if ($alreadyAssigned) {
                return back()->with('status', 'この審判員は既に指名済みです。');
            }

            // 3) 現在の assigned 件数を数える
            $currentAssigned = $locked->referees()
                ->wherePivot('status','assigned')
                ->count();

            // 4) capacity チェック（null or 0 は無制限と解釈）
            $capacity = (int)($locked->capacity ?? 0);
            if ($capacity > 0 && $currentAssigned >= $capacity) {
                return back()->with('status', '定員に達しました。');
            }

            // 5) 指名（upsert）
            $locked->referees()->syncWithoutDetaching([
                $refereeId => [
                    'status' => 'assigned',
                    'assigned_by_user_id' => $userId, // ピボットにカラムがあれば
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            ]);

            // 6) 念のため直後に再カウントして超過していないか確認（同時実行の最後尾対策）
            if ($capacity > 0) {
                $after = $locked->referees()->wherePivot('status','assigned')->count();
                if ($after > $capacity) {
                    // 超過したら取り消してエラー返却（競合時の最終防波堤）
                    $locked->referees()->detach($refereeId);
                    return back()->with('status', '同時申込が重なり定員超過となったため、指名できませんでした。');
                }
            }

            return back()->with('status', '指名しました。');
        });
    }

    /** 解除（デタッチ or ステータス戻し） */
    public function unassign(Request $request, Nomination $nomination, Referee $referee)
    {
        $chiefOrgId = $this->chiefOrgId($request);
        if ($chiefOrgId <= 0) abort(403);

        if ((int)$referee->organization_id !== $chiefOrgId) {
            return back()->with('status', '自団体の審判員のみ解除できます。');
        }

        $nomination->referees()->detach($referee->id);
        // 履歴保持したい場合は detach ではなく:
        // $nomination->referees()->updateExistingPivot($referee->id, ['status' => 'invited']);
        return back()->with('status','解除しました。');
    }
}
