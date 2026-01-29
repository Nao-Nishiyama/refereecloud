<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use App\Models\Official;
use Carbon\CarbonPeriod;
use App\Models\Competition;
use Illuminate\Http\Request;
use App\Models\NominationCapacity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CompetitionsController extends Controller
{
    private $competition;
    private $type;
    private $license;
    private $category;
    private $official;
    private $referee;

    public function __construct(Competition $competition, Type $type, License $license, Category $category, Official $official, Referee $referee)
    {
        $this->competition = $competition;
        $this->type = $type;
        $this->license = $license;
        $this->category = $category;
        $this->official = $official;
        $this->referee = $referee;
    }

    public function index()
    {
        return view('admin.competitions.index');
    }

    public function show()
    {
        $competitions = $this->competition->orderBy('start_day', 'desc')->get();
        $all = Competition::withTrashed()->orderBy('start_day', 'desc')->get();
        $trashed = Competition::onlyTrashed()->orderBy('start_day', 'desc')->get();

        return view('admin.competitions.show', compact('competitions', 'all', 'trashed'));
    }

    public function create()
    {
        $all_types = $this->type->all();
        return view('admin.competitions.create', compact('all_types'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'required|exists:types,id',
            'start_day' => 'required|date',
            'end_day' => 'required|date|after_or_equal:start_day',
            'city' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'application_deadline' => 'nullable|date|before_or_equal:end_day',
            'organizer_message' => 'nullable|string',
            'admin_private_note' => 'nullable|string',
        ]);

        $competition = Competition::create($validated);

        return redirect()->route('competitions.show')->with('success', '大会を登録しました。');
    }

    public function edit($id)
    {
        $competition = Competition::with([
            'officials',
            'licenses',
            'categories',
            'nominations.day',
            'nominations.official',
            'nominations.referees.categories'
        ])->findOrFail($id);

        $period    = CarbonPeriod::create($competition->start_day, '1 day', $competition->end_day);
        $dates     = collect($period)->values();
        $datesList = $dates;

        $allowedLicenseIds  = $competition->licenses->pluck('id')->all();
        $allowedCategoryIds = $competition->categories->pluck('id')->all();

        $prechecked = $competition->nominations
            ->mapWithKeys(function ($n) {
                $dateStr = $n->day->date instanceof Carbon
                    ? $n->day->date->toDateString()
                    : Carbon::parse($n->day->date)->toDateString();

                return [$n->official_id.'|'.$dateStr => true];
            })->all();

        $cellFilters = $competition->nominations()
            ->with('day:id,competition_id,date')
            ->get()
            ->mapWithKeys(function ($n) {
                $dateStr = $n->day->date->toDateString();
                $f = $n->filters_json ?? [];

                $toIntArr = fn($a) => array_values(array_filter(array_map('intval', (array)($a ?? []))));
            return [
                $n->official_id.'|'.$dateStr => [
                    'license_ids'  => $toIntArr($f['license_ids']  ?? []),
                    'category_ids' => $toIntArr($f['category_ids'] ?? []),
                ],
            ];
        })
        ->all();

        $all_types      = $this->type->all();
        $all_licenses   = $this->license->all();
        $all_categories = $this->category->all();
        $all_officials  = $this->official->all();

        return view('admin.competitions.edit', compact(
            'competition',
            'dates',
            'datesList',
            'prechecked', 
            'all_types',
            'all_licenses',
            'all_categories',
            'all_officials',
            'cellFilters'
        ));
    }

    public function update(Request $request, $id)
    {
        $competition = $this->competition->findOrFail($id);
        
        $data = $request->validate([
            'name'                  => ['required','string','max:255'],
            'type_id'               => ['required','integer'],
            'start_day'             => ['required','date'],
            'end_day'               => ['required','date','after_or_equal:start_day'],
            'city'                  => ['nullable','string','max:255'],
            'venue'                 => ['nullable','string','max:255'],
            'application_deadline'  => ['nullable','date'],
            'organizer_message'     => ['nullable','string'],
            'admin_private_note'    => ['nullable','string'],
            'license'               => ['array'],
            'license.*'             => ['integer'],
            'category'              => ['array'],
            'category.*'            => ['integer'],
            'combos'                => ['array'], 
        ]);

        $combos = (array) $request->input('combos', []);

        $cells = [];
        $wantedDates = [];

        foreach ($combos as $officialId => $byKind) {
            foreach ((array)$byKind as $kind => $byCol) {
                if (!in_array($kind, ['license','category'], true)) continue;
                foreach ((array)$byCol as $colId => $dates) {
                    foreach ((array)$dates as $d) {
                        try {
                            $ds = Carbon::parse($d)->toDateString();
                        } catch (\Throwable $e) {
                            continue;
                        }
                        $key = ((int)$officialId).'|'.$ds;

                        $cells[$key] ??= ['license_ids'=>[], 'category_ids'=>[]];
                        if ($kind === 'license')  { $cells[$key]['license_ids'][(int)$colId]  = true; }
                        if ($kind === 'category') { $cells[$key]['category_ids'][(int)$colId] = true; }

                        $wantedDates[$ds] = true;
                    }
                }
            }
        }

        $period = collect(Carbon::parse($data['start_day'])
            ->daysUntil(Carbon::parse($data['end_day'])->addDay()))
            ->map->toDateString()->flip()->all();

        $cells = array_filter($cells, function($_, $k) use ($period) {
            [, $date] = explode('|', $k, 2);
            return isset($period[$date]);
        }, ARRAY_FILTER_USE_BOTH);

        $wantedDates = array_values(array_filter(array_keys($wantedDates), fn($d)=>isset($period[$d])));

        DB::transaction(function () use ($competition, $data, $wantedDates, $cells) {

            $competition->fill([
                'name'                 => $data['name'],
                'type_id'              => $data['type_id'],
                'start_day'            => $data['start_day'],
                'end_day'              => $data['end_day'],
                'city'                 => $data['city'] ?? null,
                'venue'                => $data['venue'] ?? null,
                'application_deadline' => $data['application_deadline'] ?? null,
                'organizer_message'    => $data['organizer_message'] ?? null,
                'admin_private_note'   => $data['admin_private_note'] ?? null,
            ])->save();

            if (!empty($wantedDates)) {
                $dayRows = array_map(fn($d)=>[
                    'competition_id' => $competition->id,
                    'date'           => $d,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ], $wantedDates);

                DB::table('competition_days')->upsert(
                    $dayRows,
                    ['competition_id','date'],
                    ['updated_at']
                );
            }

            $dayMap = $competition->days()
                ->whereIn('date', $wantedDates)
                ->pluck('id','date')
                ->all();

            $nomRows = [];
            foreach ($cells as $key => $filters) {
                [$officialId, $date] = explode('|', $key, 2);
                if (!isset($dayMap[$date])) continue;

                $licenseIds  = array_values(array_map('intval', array_keys($filters['license_ids'])));
                $categoryIds = array_values(array_map('intval', array_keys($filters['category_ids'])));

                $nomRows[] = [
                    'competition_id' => $competition->id,
                    'day_id'         => $dayMap[$date],
                    'official_id'    => (int)$officialId,
                    'filters_json'   => json_encode([
                        'license_ids'  => $licenseIds,
                        'category_ids' => $categoryIds,
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            if (!empty($nomRows)) {
                DB::table('nominations')->upsert(
                    $nomRows,
                    ['competition_id','day_id','official_id'],
                    ['filters_json','updated_at']
                );

                $keepKeys = array_map(fn($r)=>$r['official_id'].'|'.$r['day_id'], $nomRows);
                $quoted   = implode(',', array_map(fn($k)=>DB::getPdo()->quote($k), $keepKeys));

                $competition->nominations()
                    ->when($keepKeys, function($q) use ($quoted) {
                        $q->whereRaw("CONCAT(official_id,'|',day_id) NOT IN ($quoted)");
                    }, function($q){
                        $q->whereRaw('1=1');
                    })
                    ->delete();
            } else {
                $competition->nominations()->delete();
            }
        });

        return back()->with('status', '更新しました。');
    }

    public function destroy(Competition $competition)
    {
        $competition->delete(); // 論理削除（子も deleting イベントで論理削除）
        return back()->with('status','大会を削除しました（復元可能）');
    }

    public function restore($id)
    {
        $competition = Competition::onlyTrashed()->findOrFail($id);
        $competition->restore(); // 復元（子も restoring で復元）
        return back()->with('status','大会を復元しました');
    }

    public function forceDestroy($id)
    {
        $competition = Competition::withTrashed()->findOrFail($id);
        $competition->forceDelete(); // 物理削除（子も booted で forceDelete）
        return redirect()->route('admin.competitions.index')->with('status','大会を完全に削除しました');
    }


    // public function showDetail($id)
    // {
    //     $competition = $this->competition->findOrFail($id);
    //     $referees = $this->referee->where('organization_id', Auth::user()->referee->organization_id)->orderBy('license_id')->orderBy('surname_kana')->get();

    //     $period = CarbonPeriod::create($competition->start_day, $competition->end_day);
    //     $dates = collect($period)->map(function (Carbon $date) {
    //         return $date;
    //     });
        
    //     // chief の所属 org を取得（パターンB）
    //     $chiefOrgId = optional(
    //         $this->referee->where('user_id', Auth::user()->id)->first()
    //     )->organization_id;

    //     abort_if(!$chiefOrgId, 403, '審判長の所属が未設定です。');

    //     $competition->load([
    //         'type',
    //         'nominations.day',
    //         'nominations.official',
    //         'nominations.referees' => fn($q)=>$q->wherePivot('status','assigned'),
    //     ]);

    //     // 事前選択: nomination_id => [referee_id...]
    //     $preAssigned = [];
    //     foreach ($competition->nominations as $n) {
    //         $preAssigned[$n->id] = $n->referees->pluck('id')->values()->all();
    //     }

    //     // nominationごとの候補者リストを作る
    //     $candidatesByNomination = [];

    //     foreach ($competition->nominations as $n) {
    //             if (Auth::user()->role_id === 1 || Auth::user()->role_id === 2) {
    //                 $f = (array)($n->filters_json ?? []);
    //                 $licenseIds  = array_map('intval', (array)($f['license_ids']  ?? []));
    //                 $categoryIds = array_map('intval', (array)($f['category_ids'] ?? []));

    //                 $candidatesByNomination[$n->id] = $this->referee->query()
    //                     ->eligibleFor($licenseIds, $categoryIds)
    //                     ->orderBy('id')
    //                     ->get();
    //             } else {
    //             $f = (array)($n->filters_json ?? []);
    //             $licenseIds  = array_map('intval', (array)($f['license_ids']  ?? []));
    //             $categoryIds = array_map('intval', (array)($f['category_ids'] ?? []));

    //             // filters が空でも「自団体」のみは必ず担保
    //             $candidatesByNomination[$n->id] = $this->referee->query()
    //                 ->eligibleFor($licenseIds, $categoryIds)          // license/category 条件
    //                 ->where('organization_id', (int)$chiefOrgId)      // 自団体のみ
    //                 ->with('organization:id,short_name')
    //                 ->orderBy('id')
    //                 ->get();
    //             }
    //     }
    //     // 並び順：日付→役職
    //     $nominations = $competition->nominations->sortBy([
    //         ['day.date','asc'],
    //         ['official.id','asc'],
    //     ])->values();

    //     $myOrgId = optional(Auth::user()->referee)->organization_id ?? 0;

    //     // 画面に出す nomination のみに絞って、自団体の枠数を取得（soft deleteは自動で除外）
    //     $capByNomination = NominationCapacity::where('organization_id', $myOrgId)
    //         ->whereIn('nomination_id', $nominations->pluck('id'))
    //         ->pluck('capacity', 'nomination_id');   // [nomination_id => capacity]
            
    //     return view('admin.competitions.detail.show', compact(
    //         'competition', 'nominations', 'preAssigned', 'candidatesByNomination', 'capByNomination', 'referees'
    //     ));
    // }

    public function showDetail($id)
    {
        $competition = $this->competition->findOrFail($id);

        $chiefOrgId = optional(
            $this->referee->where('user_id', Auth::user()->id)->first()
        )->organization_id;

        abort_if(!$chiefOrgId, 403, '審判長の所属が未設定です。');

        $competition->load([
            'type',
            'nominations.day',
            'nominations.official',
            'nominations.referees' => fn($q)=>$q->wherePivot('status','assigned'),
        ]);

        // 並び順：日付→役職
        $nominations = $competition->nominations->sortBy([
            ['day.date','asc'],
            ['official.id','asc'],
        ])->values();

        // 事前選択: nomination_id => [referee_id...]
        $preAssigned = [];
        foreach ($nominations as $n) {
            $preAssigned[$n->id] = $n->referees->pluck('id')->values()->all();
        }

        // nominationごとの候補者リスト
        $candidatesByNomination = [];
        foreach ($nominations as $n) {
            $f = (array)($n->filters_json ?? []);
            $licenseIds  = array_map('intval', (array)($f['license_ids']  ?? []));
            $categoryIds = array_map('intval', (array)($f['category_ids'] ?? []));

            $q = $this->referee->query()
                ->eligibleFor($licenseIds, $categoryIds)
                ->with('organization:id,short_name')
                ->orderBy('license_id');

            if (!(Auth::user()->role_id === 1 || Auth::user()->role_id === 2)) {
                $q->where('organization_id', (int)$chiefOrgId);
            }

            $candidatesByNomination[$n->id] = $q->get();
        }

        $myOrgId = (int)(optional(Auth::user()->referee)->organization_id ?? 0);

        $capByNomination = NominationCapacity::query()
            ->where('organization_id', $myOrgId)
            ->whereIn('nomination_id', $nominations->pluck('id'))
            ->pluck('capacity', 'nomination_id')
            ->map(fn($v) => (int)$v)
            ->all(); // ←配列化

        return view('admin.competitions.detail.show', compact(
            'competition',
            'nominations',
            'preAssigned',
            'candidatesByNomination',
            'capByNomination'
        ));
    }

    public function assign(Request $request, $id)
    {
        // assignments[<nomination_id>][] = referee_id
        $data = $request->validate(['assignments' => 'array']);
        $assignments = $data['assignments'] ?? [];
        $competition = $this->competition->findOrFail($id);
        
        DB::transaction(function () use ($competition, $assignments) {
            $competition->load('nominations');

            foreach ($competition->nominations as $n) {
                $incoming = array_values(array_unique(array_filter(
                    array_map('intval', $assignments[$n->id] ?? [])
                )));
                // いったん「2枠」運用（将来は $n->capacity や org別枠に差し替え）
                $incoming = array_slice($incoming, 0, 2);

                // 現在 assigned のID
                $current = $n->referees()
                    ->wherePivot('status','assigned')
                    ->pluck('referees.id')->all();

                // 外すべき（今回未選択になった）assigned
                $toDetach = array_diff($current, $incoming);
                if (!empty($toDetach)) {
                    // status='assigned' の行だけを削除（他statusは保持）
                    $n->referees()->newPivotStatement()
                        ->where('nomination_id', $n->id)
                        ->whereIn('referee_id', $toDetach)
                        ->where('status','assigned')
                        ->delete();
                }

                // 付ける/更新する
                if (!empty($incoming)) {
                    $attach = [];
                    foreach ($incoming as $rid) {
                        $attach[$rid] = [
                            'status'     => 'assigned',
                            'meta_json'  => json_encode([
                                'set_by' => Auth::user()->id,
                                'at'     => now()->toIso8601String(),
                            ], JSON_UNESCAPED_UNICODE),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $n->referees()->syncWithoutDetaching($attach);
                }
            }
        });

        return back()->with('status','割当を保存しました。');
    }

}