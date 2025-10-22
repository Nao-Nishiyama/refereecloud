<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\License;
use App\Models\Referee;
use App\Models\AnnualReport;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;

class RefereeReportsController extends Controller
{
    private $referee;
    private $user;
    private $annual_report;
    protected $signature = 'referees:update-approvals {year?}';
    protected $description = '年度ごとのapproved/suspendedを更新';

    public function __construct(Referee $referee, User $user, AnnualReport $annual_report)
    {
        $this->user = $user;
        $this->referee = $referee;
        $this->annual_report = $annual_report;
    }

    public function show(Request $request)
    {
        $licId = $request->filled('license') ? (int)$request->license : null;
        $orgId = $request->filled('organization') ? (int)$request->organization : null;

        // '', 'with', 'only' 以外は '' に落とす
        $mode = (string) $request->query('trashed', '');
        if (!in_array($mode, ['', 'with', 'only'], true)) {
            $mode = '';
        }

        $canViewTrashed = Gate::allows('referees.viewTrashed');

        $q = \App\Models\Referee::query()->with(['license','organization'])
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId));

        if ($canViewTrashed) {
            if ($mode === 'with')  $q->withTrashed();
            if ($mode === 'only')  $q->onlyTrashed();
        }

        // セレクト用マスタ
        $lics = License::orderBy('id')->get();
        $orgs = Organization::orderBy('id')->get();
        
        $allRefs = $this->referee->orderBy('organization_id')->orderBy('registration_number')
            ->paginate(50)->withQueryString();

        // 一覧データ（資格×団体のAND）
        $refs = Referee::query()
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())
            ->orderBy('organization_id')->orderBy('registration_number')
            ->get(); // ページングしたければ ->paginate(20)->withQueryString()

        // 件数ファセット（もう一方の条件だけ当てる）
        $countsByLic = \App\Models\Referee::query()
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())
            ->selectRaw('license_id, COUNT(*) as cnt')->groupBy('license_id')->pluck('cnt','license_id');

        $countsByOrg = \App\Models\Referee::query()
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())
            ->selectRaw('organization_id, COUNT(*) as cnt')->groupBy('organization_id')->pluck('cnt','organization_id');

        return view('admin.referees.reports.show', [
            'refs' => $refs,
            'licId' => $licId,
            'orgId' => $orgId,
            'mode' => $mode,
            'canViewTrashed' => $canViewTrashed,
            'countsByLic' => $countsByLic,
            'countsByOrg' => $countsByOrg,
            'lics' => $lics,
            'orgs' => $orgs,
            'allRefs' => $allRefs,
        ]);
    }

    public function indRepShow($id)
    {
        $user = $this->user->findOrFail($id);
        $reports = $this->annual_report->where('user_id', $user->id)->orderBy('year', 'desc')->get();

        return view('users.reports.show', compact('reports', 'user'));
    }
    
}
