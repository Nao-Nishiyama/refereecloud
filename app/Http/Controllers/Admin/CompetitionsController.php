<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\Type;
use App\Models\License;
use App\Models\Referee;
use App\Models\Organization;
use App\Models\Category;
use App\Models\Official;
use App\Models\Competition;
use App\Models\NominationCapacity;
use App\Http\Controllers\Controller;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class CompetitionsController extends Controller
{
    private $competition;
    private $organization;
    private $type;
    private $license;
    private $category;
    private $official;
    private $referee;

    public function __construct(Competition $competition, Type $type, Organization $organization, License $license, Category $category, Official $official, Referee $referee)
    {
        $this->competition = $competition;
        $this->type = $type;
        $this->organization = $organization;
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


    public function showDetail($id)
    {
        $competition = $this->competition->findOrFail($id);

        $isAdmin     = Auth::user()->role_id === 1;
        $isCommittee = Auth::user()->role_id === 2;

        $myOrgId = (int) optional(Auth::user()->referee)->organization_id;
        abort_if(!$myOrgId, 403, '所属団体が未設定です。');

        // 団体は id 順（←表示順の元）
        $organizations = $this->organization->orderBy('id')->get(['id','short_name','full_name']);

        // nominations 読み込み
        $competition->load([
            'type',
            'nominations.day',
            'nominations.official',
            // assigned を見るため（※このload自体は必須ではないが残してOK）
            'nominations.referees' => fn($q)=>$q->wherePivot('status','assigned'),
        ]);

        $nominations = $competition->nominations->sortBy([
            ['day.date','asc'],
            ['official.id','asc'],
        ])->values();

        // 表示対象団体：admin/committee は全部、chief は自団体のみ
        $orgIdsForView = ($isAdmin || $isCommittee)
            ? $organizations->pluck('id')->all()
            : [$myOrgId];

        // ① capacity: [nomId][orgId] => capacity
        $capRows = \App\Models\NominationCapacity::query()
            ->whereIn('nomination_id', $nominations->pluck('id'))
            ->whereIn('organization_id', $orgIdsForView)
            ->get(['nomination_id','organization_id','capacity']);

        $capMatrix = [];
        foreach ($capRows as $row) {
            $capMatrix[(int)$row->nomination_id][(int)$row->organization_id] = (int)$row->capacity;
        }

        // ② preAssigned: [nomId][orgId] => [referee_id...] （slot_no順）
        $preAssignedMatrix = [];
        foreach ($nominations as $n) {
            $rows = $n->referees()
                ->wherePivot('status', 'assigned')
                ->orderByPivot('organization_id')
                ->orderByPivot('slot_no')
                ->get(['referees.id']);

            foreach ($rows as $r) {
                $orgId = (int) $r->pivot->organization_id;
                $preAssignedMatrix[$n->id][$orgId][] = (int) $r->id;
            }
        }

        // ③ 候補者: [nomId][orgId] => Collection<Referee>
        $candidatesByNominationOrg = [];
        foreach ($nominations as $n) {
            $f = (array)($n->filters_json ?? []);
            $licenseIds  = array_map('intval', (array)($f['license_ids']  ?? []));
            $categoryIds = array_map('intval', (array)($f['category_ids'] ?? []));

            foreach ($orgIdsForView as $orgId) {
                $candidatesByNominationOrg[$n->id][$orgId] = $this->referee->query()
                    ->eligibleFor($licenseIds, $categoryIds)
                    ->where('organization_id', (int)$orgId)
                    ->with('organization:id,short_name')
                    ->orderBy('license_id')
                    ->orderBy('surname_kana')
                    ->get();
            }
        }

        $orgMap = $organizations->pluck('short_name','id')->all();

        // 必要人数・集計人数（nominationごと）
        $needTotalByNomination = [];
        $assignedTotalByNomination = [];

        foreach ($nominations as $n) {
            $nomId = (int)$n->id;

            // 必要人数 = capMatrix の合計
            $needTotalByNomination[$nomId] = array_sum($capMatrix[$nomId] ?? []);

            // 集まっている人数 = preAssignedMatrix の全orgの合計（配列数の合計）
            $assignedTotalByNomination[$nomId] = collect($preAssignedMatrix[$nomId] ?? [])
                ->sum(fn($arr) => is_array($arr) ? count($arr) : 0);
        }

        return view('admin.competitions.detail.show', compact(
            'competition',
            'nominations',
            'organizations',
            'orgMap',
            'capMatrix',
            'preAssignedMatrix',
            'candidatesByNominationOrg',
            'needTotalByNomination',
            'assignedTotalByNomination'
        ));
    }

    public function assign(Request $request, $id)
    {
        $data = $request->validate([
            'assignments' => ['nullable', 'array'],
            'assignments.*' => ['array'],          // nomination
            'assignments.*.*' => ['array'],        // org
            'assignments.*.*.*' => ['nullable', 'integer'], // referee_id
        ]);

        $assignments = $data['assignments'] ?? [];
        $competition = $this->competition->findOrFail($id);

        $isAdmin     = Auth::user()->role_id === 1;

        $myOrgId = (int) optional(Auth::user()->referee)->organization_id;
        abort_if(!$myOrgId, 403, '所属団体が未設定です。');

        DB::transaction(function () use ($competition, $assignments, $isAdmin, $myOrgId) {

            $nominationIds = array_map('intval', array_keys($assignments));

            $nominations = $competition->nominations()
                ->whereIn('id', $nominationIds)
                ->get();

            foreach ($nominations as $n) {
                $byOrg = $assignments[$n->id] ?? [];

                // admin以外は自団体だけ
                if (!$isAdmin) {
                    $byOrg = [(string)$myOrgId => ($byOrg[$myOrgId] ?? [])];
                }

                foreach ($byOrg as $orgIdStr => $refIds) {
                    $orgId = (int) $orgIdStr;

                    if (!$isAdmin && $orgId !== $myOrgId) continue;

                    $slots = (int) \App\Models\NominationCapacity::where('nomination_id', $n->id)
                        ->where('organization_id', $orgId)
                        ->value('capacity');

                    $incoming = array_values(array_filter(array_map('intval', $refIds ?? [])));

                    // ★重複除去（同じ人が複数枠に入っても1回にする）
                    $incoming = array_values(array_unique($incoming));

                    // 枠数まで
                    $incoming = array_slice($incoming, 0, max($slots, 0));

                    // 念のため「その団体所属の審判だけ」通す（不正入力対策）
                    if (!empty($incoming)) {
                        $valid = \App\Models\Referee::where('organization_id', $orgId)
                            ->whereIn('id', $incoming)
                            ->pluck('id')
                            ->map(fn($v)=>(int)$v)
                            ->all();

                        $incoming = array_values(array_filter($incoming, fn($rid)=>in_array((int)$rid, $valid, true)));
                    }

                    // nomination×org の assigned を入れ替え（他団体は触らない）
                    $n->referees()->newPivotStatement()
                        ->where('nomination_id', $n->id)
                        ->where('organization_id', $orgId)
                        ->where('status', 'assigned')
                        ->delete();

                    foreach ($incoming as $i => $rid) {
                        $n->referees()->attach($rid, [
                            'organization_id' => $orgId,
                            'slot_no'         => $i + 1,
                            'status'          => 'assigned',
                            'meta_json'       => json_encode([
                                'set_by' => Auth::user()->id,
                                'at'     => now()->toIso8601String(),
                            ], JSON_UNESCAPED_UNICODE),
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }
            }
        });

        return back()->with('status','割当を保存しました。');
    }

}