<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use App\Models\Nomination;
use App\Models\Application;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\NominationReferee;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    private $competition;
    private $nomination;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    public function apply($id)
    {
        $competition = Competition::with([
            'type',
            'competitionLicense.license',
            'competitionCategory.category',
            'nominations.day',        // day->date
            'nominations.official',   // official 名
        ])->findOrFail($id);

        // ログイン中の Referee（単一FK と 多対多の両方に配慮）
        $ref = Referee::with('categories:id')->where('user_id', Auth::user()->id)->first();

        $eligibleCells = collect();
        $licenseNames  = [];
        $categoryNames = [];

        if ($ref) {
            $myLicense   = (int)($ref->license_id ?? 0);
            $catIdsFk    = isset($ref->category_id) ? [(int)$ref->category_id] : [];
            $catIdsRel   = $ref->relationLoaded('categories') ? $ref->categories->pluck('id')->map(fn($v)=>(int)$v)->all() : [];
            $myCategories = array_values(array_unique(array_filter(array_merge($catIdsFk, $catIdsRel))));

            // display 用に名前辞書（必要なら一括ロード）
            $licenseNames  = License::pluck('name','id')->map(fn($v)=> (string)$v)->all();
            $categoryNames = Category::pluck('name','id')->map(fn($v)=> (string)$v)->all();

            $eligibleCells = $competition->nominations
                ->filter(function ($cell) use ($myLicense, $myCategories) {
                    $f    = (array)($cell->filters_json ?? []);
                    $lics = array_map('intval', (array)($f['license_ids']  ?? []));
                    $cats = array_map('intval', (array)($f['category_ids'] ?? []));

                    $licenseMatch  = $myLicense && in_array($myLicense, $lics, true);
                    $categoryMatch = !empty(array_intersect($myCategories, $cats));

                    return $licenseMatch || $categoryMatch;
                })
                ->map(function ($cell) use ($licenseNames, $categoryNames) {
                    $f    = (array)($cell->filters_json ?? []);
                    $lics = array_map('intval', (array)($f['license_ids']  ?? []));
                    $cats = array_map('intval', (array)($f['category_ids'] ?? []));

                    return [
                        'nomination_id' => $cell->id, // ★ 追加
                        'official'      => $cell->official?->name ?? '-',
                        'date'          => optional($cell->day?->date)->toDateString(),
                        'license_names' => collect($lics)->map(fn($id)=>$licenseNames[$id] ?? "ID:$id")->values()->all(),
                        'category_names'=> collect($cats)->map(fn($id)=>$categoryNames[$id] ?? "ID:$id")->values()->all(),
                    ];
                })
                ->sortBy(['date','official'])
                ->values();
        }

        $appliedNominationIds = Application::where('user_id', Auth::id())
            ->whereHas('nomination', fn($q) => $q->where('competition_id', $competition->id))
            ->pluck('nomination_id')
            ->map(fn($v) => (int)$v)
            ->all();

        // 画面に出す候補セルの nomination_id を集める
        $eligibleNominationIds = collect($eligibleCells)->pluck('nomination_id')->filter()->unique()->values();

        // 自分（ログインユーザー）が Referee と紐付いている前提
        $myRefId = optional(Auth::user()->referee)->id;

        $appointedNominationIds = NominationReferee::query()
            ->where('referee_id', $myRefId)
            ->whereIn('nomination_id', $eligibleNominationIds)
            ->pluck('nomination_id')
            ->map(fn($v) => (int)$v)
            ->all();
            
        // 受付締切の判定（当日いっぱい有効にする例）
        $deadline = $competition->application_deadline
            ? Carbon::parse($competition->application_deadline)->endOfDay()
            : null;
        $isClosed = $deadline ? $deadline->isPast() : false;
            
        return view('users.competitions.apply', compact(
            'competition','eligibleCells','isClosed','appliedNominationIds', 'appointedNominationIds'
        ));
    }

    public function store(Request $request, Competition $competition)
    {
        $validated = $request->validate([
            'nomination_id' => [
                'required','integer',
                Rule::exists('nominations','id')->where(
                    fn($q) => $q->where('competition_id', $competition->id)
                ),
            ],
        ]);

        $nominationId = (int)$validated['nomination_id'];

        // 締切チェック（過去は申込不可）
        if ($competition->application_deadline && now()->gt($competition->application_deadline)) {
            return back()->with('status','受付締切を過ぎています。');
        }

        // upsert 的に作成（ユニーク制約で守られている）
        Application::updateOrCreate([
            'user_id'        => Auth::user()->id,
            'nomination_id'  => $nominationId,
        ], [
            'status' => 'applied',
        ]);

        return back()->with('status','申込を受け付けました。');
    }

    public function destroy(Competition $competition, Nomination $nomination)
    {
        $userId = Auth::id();

        // 紐付け整合
        if ($nomination->competition_id !== $competition->id) {
            abort(404);
        }

        // 締切前のみ取消可能（締切後は主催へ連絡 等、運用に合わせて）
        if ($competition->application_deadline && now()->gt($competition->application_deadline)) {
            return back()->with('status','締切後は取消できません。');
        }

        Application::where('user_id', $userId)
            ->where('nomination_id', $nomination->id)
            ->delete();

        return back()->with('status','申込を取消しました。');
    }

}
