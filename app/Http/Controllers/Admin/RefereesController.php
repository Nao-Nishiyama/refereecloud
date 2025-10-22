<?php

namespace App\Http\Controllers\Admin;

use App\Models\Type;
use App\Models\User;
use App\Models\License;
use App\Models\Referee;
use App\Models\Category;
use App\Models\Official;
use App\Models\Competition;
use Illuminate\Support\Str;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\RefereeApproval;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\LengthAwarePaginator;

class RefereesController extends Controller
{
    private $user;
    private $referee;
    private $competition;
    private $type;
    private $license;
    private $category;
    private $official;

    private function currentFiscalYear(): int
    {
        $now = now();
        return $now->month >= 4 ? $now->year : $now->subYear()->year; // 4〜12月: 今年 / 1〜3月: 昨年
    }

    public function __construct(User $user, Referee $referee, Competition $competition, Type $type, License $license, Category $category, Official $official)
    {
        $this->user = $user;
        $this->referee = $referee;
        $this->competition = $competition;
        $this->type = $type;
        $this->license = $license;
        $this->category = $category;
        $this->official = $official;
    }

    public function index()
    {
        return view('admin.referees.index');
    }

    public function show(Request $request)
    {
        $licId = $request->has('license') ? $request->integer('license') : null;
        $orgId = $request->has('organization') ? $request->integer('organization') : null;

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

        $allRefs = $this->referee->orderBy('organization_id')->orderBy('registration_number')
            ->paginate(50)->withQueryString();

        // 一覧データ（資格×団体のAND）
        $refs = Referee::query()
            ->when(!is_null($licId), fn($q) => $q->where('license_id', $licId))
            ->when(!is_null($orgId), fn($q) => $q->where('organization_id', $orgId))
            ->when($canViewTrashed && $mode==='only', fn($q) => $q->onlyTrashed())
            ->when($canViewTrashed && $mode==='with',  fn($q) => $q->withTrashed())            ->orderBy('organization_id')->orderBy('registration_number')
            ->get(); // ページングしたければ ->paginate(20)->withQueryString()

        $refsByLic = Referee::query()->where('license_id', $licId)->get();
        $refsByOrg = Referee::query()->where('organization_id', $orgId)->get();

        // 左のカウント（モード反映）
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

        return view('admin.referees.show', [
            'refs' => $refs,
            'licId' => $licId,
            'orgId' => $orgId,
            'refsByLic' => $refsByLic,
            'refsByOrg' => $refsByOrg,
            'mode' => $mode,
            'canViewTrashed' => $canViewTrashed,
            'countsByLic' => $countsByLic,
            'countsByOrg' => $countsByOrg,
            'allRefs' => $allRefs,
            'lics' => \App\Models\License::orderBy('id')->get(),
            'orgs' => \App\Models\Organization::orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        $this->authorize('referees.create');

        return view('admin.referees.create', [
            'licenses'     => \App\Models\License::orderBy('id')->get(),
            'organizations'=> \App\Models\Organization::orderBy('id')->get(),
            'prefectures'=> \App\Models\Prefecture::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        // バリデーション（登録自体の条件）
        $v = $request->validate([
            'surname_kanji' => ['required','string','max:50'],
            'name_kanji'    => ['required','string','max:50'],
            'surname_kana'  => ['required','string','max:50'],
            'name_kana'     => ['required','string','max:50'],
            'surname'       => ['required','string','max:50'], // 英字姓
            'name'          => ['required','string','max:50'], // 英字名
            'registration_number' => ['nullable','string','max:50'],
            'license_id'     => ['required','integer','exists:licenses,id'],
            'organization_id'=> ['required','integer','exists:organizations,id'],
            'prefecture_id'  => ['required','integer','exists:prefectures,id'],
            'birth_date'     => ['required','date'],
            'mrs_member_id'  => ['nullable','string','max:50'],
            'remarks'        => ['nullable','string','max:2000'],
        ]);

        // ① 重複疑いの検索（漢字/カナ/英字のどれかのセット一致）
        $dups = $this->findPossibleDuplicates($v);

        if ($dups->isNotEmpty()) {
            // 保存せずに、create へ戻してモーダルを出す
            // 一覧表示で使うために関連も少し付ける
            $dups->load(['license','organization']);

            return back()
                ->withInput()
                ->with([
                    'dup_candidates' => $dups->take(10), // モーダルに出す候補
                    'dup_payload'    => $v,              // 連絡フォームへ引き継ぐ用
                ]);
        }

        // ② 重複なし → 登録（必要なら前の回答の「承認レコード自動作成」もここで）
        $ref = null;
        DB::transaction(function () use (&$ref, $v) {
            $ref = Referee::create([
                'user_id'            => null,
                'surname'            => $v['surname'],
                'name'               => $v['name'],
                'surname_kanji'      => $v['surname_kanji'],
                'name_kanji'         => $v['name_kanji'],
                'surname_kana'       => $v['surname_kana'],
                'name_kana'          => $v['name_kana'],
                'registration_number'=> $v['registration_number'] ?? null,
                'prefecture_id'      => $v['prefecture_id'],
                'organization_id'    => $v['organization_id'],
                'license_id'         => $v['license_id'],
                'birth_date'         => $v['birth_date'],
                'mrs_member_id'      => $v['mrs_member_id'] ?? null,
                'remarks'            => $v['remarks'] ?? null,
            ]);

            // 今年度の承認レコードを「未承認・停止中」で自動作成（必要なら）
            $year = now()->month >= 4 ? now()->year : now()->subYear()->year;
            $ref->approval()->create([
                'year'      => $year,
                'approved'  => false,
                'suspended' => true,
            ]);
        });

        return redirect()->route('admin.referees.show')
            ->with('status', '審判員を登録しました');
    }

    private function findPossibleDuplicates(array $v)
    {
        // 余白・全角/半角・大小など軽く正規化
        $norm = fn($s) => $s !== null
            ? mb_strtolower(trim(mb_convert_kana($s, 'asKV')),'UTF-8')
            : null;

        $skj = $norm($v['surname_kanji'] ?? null);
        $nkj = $norm($v['name_kanji'] ?? null);
        $ska = $norm($v['surname_kana'] ?? null);
        $nka = $norm($v['name_kana'] ?? null);
        $sro = $norm($v['surname'] ?? null);
        $nro = $norm($v['name'] ?? null);

        return Referee::query()
            ->where(function($q) use($skj,$nkj,$ska,$nka,$sro,$nro) {
                // 漢字セット
                if ($skj && $nkj) {
                    $q->orWhere(function($qq) use($skj,$nkj) {
                        $qq->whereRaw('LOWER(TRIM(CONVERT(surname_kanji USING utf8))) = ?', [$skj])
                           ->whereRaw('LOWER(TRIM(CONVERT(name_kanji    USING utf8))) = ?', [$nkj]);
                    });
                }
                // カナセット
                if ($ska && $nka) {
                    $q->orWhere(function($qq) use($ska,$nka) {
                        $qq->whereRaw('LOWER(TRIM(CONVERT(surname_kana USING utf8))) = ?', [$ska])
                           ->whereRaw('LOWER(TRIM(CONVERT(name_kana    USING utf8))) = ?', [$nka]);
                    });
                }
                // 英字セット
                if ($sro && $nro) {
                    $q->orWhere(function($qq) use($sro,$nro) {
                        $qq->whereRaw('LOWER(TRIM(CONVERT(surname USING utf8))) = ?', [$sro])
                           ->whereRaw('LOWER(TRIM(CONVERT(name    USING utf8))) = ?', [$nro]);
                    });
                }
            })
            ->take(20)
            ->get();
    }

    public function edit(Request $request, $id)
    {
        $referee = $this->referee->findOrFail($id);

        $matched = $this->user->where('surname_kana', $referee->surname_kana)
            ->where('name_kana',  $referee->name_kana)
            ->first();
        
        $q = $request->string('q')->trim()->toString();

        if (!$matched && $q !== '') {
            $candidates = User::query()
                ->where(function ($w) use ($q) {
                    $w->where('surname_kana', 'like', "%{$q}%")
                      ->orWhere('name_kana',  'like', "%{$q}%");
                })
                ->orderBy('surname_kana')->orderBy('name_kana')
                ->paginate(20)
                ->appends(['q' => $q]);
        } else {
        // 空の Paginator を返す（links() 可）
        $candidates = new LengthAwarePaginator(
            items: [],
            total: 0,
            perPage: 20,
            currentPage: 1,
            options: ['path' => $request->url(), 'query' => $request->query()]
        );
        }

        return view('admin.referees.edit')
                ->with('referee', $referee)
                ->with('matched', $matched)
                ->with('q', $q)
                ->with('candidates', $candidates);
    }

    // 紐付け: referees.user_id を更新
    public function attach(Request $request, $id)
    {
        // 1:1を担保（同じ user_id が他の Referee に既に使われていないか）
        $alreadyLinked = Referee::where('user_id', $request)
            ->where('id', '!=', $request)
            ->exists();

        if ($alreadyLinked) {
            return back()->withErrors(['user_id' => 'このユーザーは既に別のレフェリーに紐づいています。']);
        }

        $referee = $this->referee->findOrFail($id);
        $referee->user_id = $request->user_id;
        $referee->update();

        return back()->with('status', 'ユーザーをレフェリーに紐づけました。');
    }

    public function detach($id)
    {
        $referee = $this->referee->findOrFail($id);

        $referee->user->role_id = User::USER_ROLE_ID;
        $referee->user->update();

        $referee->user_id = null;
        $referee->update();


        return back()->with('status', '紐づけを解除しました。');
    }

    public function destroy(Request $request, Referee $referee, int $year)
    {
        $this->authorize('referees.delete');

        RefereeApproval::updateOrCreate(
            ['referee_id' => $referee->id],     // ← 1:1 のキー
            [
                'year'      => $year,
                'approved'  => 0,
                'suspended' => 1,
            ]
        );

        // 併せて SoftDelete を行うなら：
        if (method_exists($referee, 'delete') && !$referee->trashed()) {
            $referee->delete();
        }

        return back()->with('status', '抹消しました');
    }

    public function restore($id)
    {
        $this->authorize('referees.restore');

        $referee = \App\Models\Referee::withTrashed()->findOrFail($id);

        if ($referee->trashed()) {
            $referee->restore();
        }

        return back()->with('status', '復元しました');
    }

    // app/Http/Controllers/Admin/RefereesController.php

    public function checkDuplicate(Request $request)
    {
        // ここでは “重複判断に使う最小項目” だけを緩めに検証
        $v = $request->validate([
            'surname_kanji' => ['required','string','max:50'],
            'name_kanji'    => ['required','string','max:50'],
            'surname_kana'  => ['required','string','max:50'],
            'name_kana'     => ['required','string','max:50'],
            'surname'       => ['required','string','max:50'],
            'name'          => ['required','string','max:50'],
            'registration_number' => ['nullable','string','max:50'],
        ]);

        // 既に作ってある重複探索ロジックを流用
        $dups = $this->findPossibleDuplicates($v)->load(['license','organization']);

        return response()->json([
            'hasDuplicates' => $dups->isNotEmpty(),
            'candidates'    => $dups, // 必要なら整形して返す
        ]);
    }

}