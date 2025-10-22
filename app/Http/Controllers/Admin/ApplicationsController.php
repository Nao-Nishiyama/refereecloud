<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Referee;
use Carbon\CarbonPeriod;
use App\Models\Nomination;
use App\Models\Application;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\NominationCapacity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ApplicationsController extends Controller
{
    private $application;
    private $competition;
    private $user;
    private $referee;

    public function __construct(Application $application, Competition $competition, User $user, Referee $referee)
    {
        $this->application = $application;
        $this->competition = $competition;
        $this->user = $user;
        $this->referee = $referee;
    }

    public function show()
    {
        $competitions = $this->competition->all();
        
        $all = Competition::withTrashed()->get();
        $trashed = Competition::onlyTrashed()->get();

        $today      = now();
        $fiscalYear = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyStart    = Carbon::create($fiscalYear,     4,  1)->startOfDay();
        $fyEnd      = Carbon::create($fiscalYear + 1, 3, 31)->endOfDay();

        // $appCountThisFY = Application::query()
        //     ->whereHas('nomination.competition', function ($q) use ($fyStart, $fyEnd) {
        //         $q->whereBetween('start_day', [$fyStart, $fyEnd]);
        //     })
        //     ->count();

        // $applied_competitions = Competition::query()
        //     ->whereBetween('start_day', [$fyStart, $fyEnd])
        //     ->withCount([
        //         'nominations as applications_count' => function ($q) {
        //             $q->withCount('applications');
        //         },
        //     ])
        //     ->get();
        
        // $appliedCompetitions = Competition::query()
        //     ->whereBetween('start_day', [$fyStart, $fyEnd])
        //     ->withCount('applications')
        //     ->get();

        $comps = Competition::whereBetween('start_day', [$fyStart, $fyEnd])
            ->withCount(['applications as applicants_count' => fn($q) => $q
            ->select(DB::raw('COUNT(DISTINCT user_id)'))])
            ->get();

        return view('admin.competitions.applications', compact('competitions', 'all', 'trashed', 'comps'));
    }

    public function application($id)
    {
        $competition = $this->competition->findOrFail($id);
        $competition->load([
            'type',
            'nominations.day',
            'nominations.official',
            'nominations.referees' => fn($q)=>$q->wherePivot('status','assigned'),
        ]);

        $nominations = Nomination::with([
            'day',
            'official',
            'applications.user.referee', // ← これを追加
        ])->where('competition_id', $competition->id)
        ->orderBy('official_id')
        ->get();
            
        return view('admin.competitions.applications.show', compact(
            'competition', 'nominations'
        ));
    }
}
