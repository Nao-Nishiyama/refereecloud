<?php

namespace App\Http\Controllers\Admin;

use App\Models\License;
use App\Models\Referee;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\RefereeApproval;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class RefereeApprovalsController extends Controller
{

    private $referee;

    public function __construct(Referee $referee)
    {
        $this->referee = $referee;
    }

    public function show(Request $request)
    {
        $licId        = $request->filled('license')       ? (int) $request->input('license')       : null;
        $orgId        = $request->filled('organization')  ? (int) $request->input('organization')  : null;
        $approvedYear = $request->filled('approved_year') ? (int) $request->input('approved_year') : null;
        $onlyApplicants = $request->boolean('applicants');         // ?applicants=1

        // '', 'with', 'only' 以外は '' に落とす
        $mode = (string) $request->query('trashed', '');
        if (!in_array($mode, ['', 'with', 'only'], true)) {
            $mode = '';
        }

        $canViewTrashed = Gate::allows('referees.viewTrashed');

        // 会計年度（4/1〜翌3/31）
        $today = now();
        $fiscalYear = $today->month >= 4 ? $today->year : $today->year - 1;


        // ★ 統一クエリビルダ（関係も事前ロード）
        $q = Referee::query()
            ->with(['license','organization','approval'])
            ->when(!is_null($licId), fn($qq) => $qq->where('license_id', $licId))
            ->when(!is_null($orgId), fn($qq) => $qq->where('organization_id', $orgId));
                
        // 抹消モード
        if ($canViewTrashed) {
            if ($mode === 'with')  $q->withTrashed();
            if ($mode === 'only')  $q->onlyTrashed();
        }

        // 新規申請者（今年度 × suspended=1 × approved=0）
        if ($onlyApplicants) {
            $q->whereHas('approval', function ($w) use ($fiscalYear) {
                $w->where('year', $fiscalYear)
                ->where('suspended', 1)
                ->where('approved', 0);
            });
        }

        // 指定年度の承認済み（approved=1）
        if ($approvedYear) {
            $q->whereHas('approval', function ($w) use ($approvedYear) {
                $w->where('year', $approvedYear)
                ->where('approved', 1);
            });
        }

        // ★ 一覧データは必ず $q から取得（get or paginate）
        $refs = $q->orderBy('organization_id')
                ->orderBy('registration_number')
                ->get();

        // サイド用（null が来たらクエリしない）
        $refsByLic = Referee::query()
            ->when(!is_null($licId), fn($w) => $w->where('license_id', $licId))
            ->get();

        $refsByOrg = Referee::query()
            ->when(!is_null($orgId), fn($w) => $w->where('organization_id', $orgId))
            ->get();

        // 左のカウント（必要に応じて applicants/approved_year を反映させるなら、同様の whereHas を追加してください）
        $countsByLic = \App\Models\Referee::query()
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())
            ->selectRaw('license_id, COUNT(*) as cnt')
            ->groupBy('license_id')
            ->pluck('cnt','license_id');

        $countsByOrg = \App\Models\Referee::query()
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())
            ->selectRaw('organization_id, COUNT(*) as cnt')
            ->groupBy('organization_id')
            ->pluck('cnt','organization_id');

        // 年度選択肢
        $years = collect(range(now()->year + 1, now()->year - 4));

        return view('admin.referees.operate', [
            'refs' => $refs,
            'licId' => $licId,
            'orgId' => $orgId,
            'refsByLic' => $refsByLic,
            'refsByOrg' => $refsByOrg,
            'mode' => $mode,
            'canViewTrashed' => $canViewTrashed,
            'countsByLic' => $countsByLic,
            'countsByOrg' => $countsByOrg,
            'lics' => \App\Models\License::orderBy('id')->get(),
            'orgs' => \App\Models\Organization::orderBy('id')->get(),
            'fiscalYear' => $fiscalYear,
            'onlyApplicants' => $onlyApplicants,
            'approvedYear' => $approvedYear,
            'years' => $years,
        ]);
    }

    public function approve(\App\Models\Referee $referee, int $year)
    {
        $this->authorize('referees.approve');

        RefereeApproval::updateOrCreate(
            ['referee_id' => $referee->id],     // ← 1:1 のキー
            [
                'year'      => $year,
                'approved'  => 1,
                'suspended' => 0,
            ]
        );

        return back()->with('status', '承認しました');
    }

    public function bulkApprove(Request $request)
    {
        $this->authorize('referees.approve');

        // 会計年度（4/1〜翌3/31）を年度として扱う例
        $now = now();
        $fiscalYear = $now->month >= 4 ? $now->year : $now->year - 1;

        // 画面から渡ってくる現在のフィルタ
        $licId         = $request->filled('license')        ? (int)$request->input('license')        : null;
        $orgId         = $request->filled('organization')   ? (int)$request->input('organization')   : null;
        $mode          = in_array($request->input('trashed'), ['', 'with', 'only'], true) ? (string)$request->input('trashed') : '';
        $onlyApplicants= $request->boolean('applicants'); // 新規申請者ボタン
        $approvedYear  = $request->integer('approved_year'); // 指定があれば

        $canViewTrashed = Gate::allows('referees.viewTrashed');

        // 一覧に合わせたベースクエリ（表示されている人だけを対象にする）
        $q = Referee::query()
            ->with(['approval']) // 1:1想定
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId));

        if ($canViewTrashed) {
            if ($mode === 'with')  $q->withTrashed();
            if ($mode === 'only')  $q->onlyTrashed();
        }

        // 新規申請者（今年度 × suspended=1 × approved=0）
        if ($onlyApplicants) {
            $q->whereHas('approval', fn($w) => $w->where('year', $fiscalYear)->where('suspended', 1)->where('approved', 0));
        }

        // 「承認年度で絞り込み」しているとき → その年度の approved=1 に一致するものだけに
        if ($approvedYear) {
            $q->whereHas('approval', fn($w) => $w->where('year', $approvedYear)->where('approved', 1));
        }

        // 一括承認は「未承認」だけ対象にする（approval レコードが無い人も対象にしたいので left 的に後で upsert）
        // ここでは「approval が approved=0 or null」を対象にしたいので whereHas では絞り過ぎない。
        $targets = $q->get(); // 画面と同じ条件。あなたの一覧が paginate でなく get() ならこれで一致します

        DB::transaction(function () use ($targets, $fiscalYear) {
            foreach ($targets as $ref) {
                // 既に承認済みならスキップ
                if ((bool)($ref->approval?->approved)) {
                    continue;
                }
                // upsert：存在すれば更新、無ければ作成
                RefereeApproval::updateOrCreate(
                    ['referee_id' => $ref->id],
                    [
                        'year'      => $fiscalYear,
                        'approved'  => 1,
                        'suspended' => 0,
                    ]
                );
            }
        });

        return back()->with('status', '表示中の未承認者を一括承認しました（'. $targets->count() .'件 対象）');
    }
}
