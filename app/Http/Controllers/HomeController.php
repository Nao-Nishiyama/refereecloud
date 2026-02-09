<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Referee;
use App\Models\Prefecture;
use App\Models\Application;
use App\Models\Competition;
use App\Imports\UsersImport;
use App\Models\AnnualReport;
use Illuminate\Http\Request;
use App\Models\RefereeApproval;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    private $user;
    private $competition;
    private $role;
    private $prefecture;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(User $user, Competition $competition, Role $role, Prefecture $prefecture)
    {
        $this->middleware('auth');
        $this->user = $user;
        $this->prefecture = $prefecture;
        $this->competition = $competition;
        $this->role = $role;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index(Request $request)
    {
        $user = $request->user();

        // 基本メトリクス（適宜あなたのスキーマに合わせて）
        $today = now();
        $upcomingCompetitions = $this->competition->whereDate('start_day', '>=', $today->toDateString())->count();

        $startOfFiscalYear = $today->month >= 4
            ? Carbon::create($today->year, 4, 1)
            : Carbon::create($today->year - 1, 4, 1);

        $endOfFiscalYear = $startOfFiscalYear->copy()->addYear()->subDay(); // 翌年3/31

        $fiscalYearCompetitions = Competition::whereDate('start_day', '<=', $endOfFiscalYear)
            ->whereDate('end_day', '>=', $startOfFiscalYear)
            ->count();

        $year_comps = Competition::whereBetween('start_day', [$startOfFiscalYear, $endOfFiscalYear])
            ->orderBy('start_day', 'desc')
            ->count();

        // 今年度（4/1〜翌3/31）の年度番号
        $fiscalYear = $today->month >= 4 ? $today->year : $today->year - 1;

        // 新規申請者（今年度 × suspended=1 × approved=0）
        $pendingApplicants = RefereeApproval::where('year', $fiscalYear)
            ->where('suspended', 1)->where('approved', 0)->count();

        // 自分と同団体の審判員数（Referee←→Userの関係がある場合は適宜）
        $myOrgRefCount = optional($user->referee)->organization_id
            ? Referee::where('organization_id', $user->referee->organization_id)->count()
            : null;

        $RefCount = Referee::query()->count();
        $ReportCount = AnnualReport::query()->count();
        $ApplCount = Application::query()->count();

        // 直近の大会（3件）
        $latestCompetitions = Competition::orderByDesc('start_day')->limit(3)->get();

        // 抹消(soft deleted)を見られる権限ならカウント
        $canViewTrashed = Gate::allows('referees.viewTrashed');
        $trashedRefCount = $canViewTrashed
            ? Referee::onlyTrashed()->count()
            : null;

        return view('index', compact(
            'user',
            'upcomingCompetitions',
            'fiscalYearCompetitions',
            'pendingApplicants',
            'myOrgRefCount',
            'latestCompetitions',
            'trashedRefCount',
            'fiscalYear',
            'RefCount',
            'ReportCount',
            'ApplCount',
            'year_comps'
        ));
    }

}
