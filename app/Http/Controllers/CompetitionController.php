<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\Type;
use App\Models\User;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use Carbon\CarbonPeriod;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\CompetitionCategory;
use Illuminate\Support\Facades\Auth;

class CompetitionController extends Controller
{
    private $user;
    private $role;
    private $license;
    private $category;
    private $competition;
    private $type;
    private $competition_category;

    public function __construct(Competition $competition, Role $role, License $license, Category $category, CompetitionCategory $competition_category, User $user, Type $type)
    {
        $this->user = $user;
        $this->competition = $competition;
        $this->role = $role;
        $this->license = $license;
        $this->category = $category;
        $this->type = $type;
        $this->competition_category = $competition_category;
    }

    public function show()
    {
        $user = Auth::user();
        $ref  = Referee::with('categories:id')  // 多対多があるなら
                ->where('user_id', $user->id)->first();

        $startOfFiscalYear = Carbon::now()->month >= 4
            ? Carbon::now()->startOfYear()->addMonths(3)   // 今年4/1
            : Carbon::now()->subYear()->startOfYear()->addMonths(3); // 去年4/1

        $endOfFiscalYear = $startOfFiscalYear->copy()->addYear()->subDay(); // 翌年3/31

        $all_competitions = Competition::with(['type','nominations'])
            ->whereBetween('start_day', [$startOfFiscalYear, $endOfFiscalYear])
            ->orderBy('start_day', 'desc')
            ->get();
            
        // 対象外扱いの初期値
        $eligibleMap = [];

        if ($ref) {
            $myLicense = (int)($ref->license_id ?? 0);
            $catIdsFk  = isset($ref->category_id) ? [(int)$ref->category_id] : [];
            $catIdsRel = $ref->relationLoaded('categories') ? $ref->categories->pluck('id')->map(fn($v)=>(int)$v)->all() : [];
            $myCats    = array_values(array_unique(array_filter(array_merge($catIdsFk, $catIdsRel))));

            foreach ($all_competitions as $c) {
                $eligible = false;
                foreach ($c->nominations as $cell) {
                    $f = (array)($cell->filters_json ?? []);
                    $lic = array_map('intval', (array)($f['license_ids'] ?? []));
                    $cats = array_map('intval', (array)($f['category_ids'] ?? []));

                    // ライセンス一致 or カテゴリ一致（どちらか1つでもOK）
                    $licenseMatch  = $myLicense && in_array($myLicense, $lic, true);
                    $categoryMatch = !empty(array_intersect($myCats, $cats));

                    if ($licenseMatch || $categoryMatch) {
                        $eligible = true;
                        break;
                    }
                }
                $eligibleMap[$c->id] = $eligible;
            }
        }

        return view('competitions.show', compact('all_competitions','eligibleMap'));
    }

    public function offer()
    {
        return view('competitions.offer');
    }

    public function register(Request $request, Competition $competition)
    {
        // 前の参加日を削除（更新対応）
        $this->user->competitionRoles()->detach();

        foreach ($request->attendance as $roleId => $dates) {
            foreach ($dates as $date) {
                $this->user->competitionRoles()->attach($roleId, ['attend_date' => $date]);
            }
        }

        return redirect()->back()->with('success', '参加日を登録しました。');
    }


}
