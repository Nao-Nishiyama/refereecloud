<?php

namespace App\Http\Controllers;

use App\Models\Referee;
use App\Models\Nomination;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\CompetitionApplication;

class CompetitionApplicationController extends Controller
{
    
    public function store(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'nomination_id' => ['required','integer','exists:nominations,id'],
        ]);

        // ログイン中レフェリー
        $ref = Referee::with('categories:id')->where('user_id', Auth::user()->id)->firstOrFail();

        // 該当セル
        $nom = Nomination::with(['day','official'])
            ->where('competition_id', $competition->id)
            ->findOrFail($data['nomination_id']);

        // 受付締切（当日23:59まで受付）
        if ($competition->application_deadline) {
            $closed = Carbon::parse($competition->application_deadline)->endOfDay()->isPast();
            if ($closed) {
                return back()->with('status', '受付は締切済みです。');
            }
        }

        // 対象者判定（license OR category）
        $f = (array)($nom->filters_json ?? []);
        $lics = array_map('intval', (array)($f['license_ids']  ?? []));
        $cats = array_map('intval', (array)($f['category_ids'] ?? []));

        $myLicense = (int)($ref->license_id ?? 0);
        $catIdsFk  = isset($ref->category_id) ? [(int)$ref->category_id] : [];
        $catIdsRel = $ref->relationLoaded('categories') ? $ref->categories->pluck('id')->map(fn($v)=>(int)$v)->all() : [];
        $myCats    = array_values(array_unique(array_filter(array_merge($catIdsFk, $catIdsRel))));

        $licenseMatch  = $myLicense && in_array($myLicense, $lics, true);
        $categoryMatch = !empty(array_intersect($myCats, $cats));
        if (!$licenseMatch && !$categoryMatch) {
            return back()->with('status', '申込対象外の募集です。');
        }

        // 既存申込チェック
        $exists = CompetitionApplication::where('nomination_id', $nom->id)
            ->where('referee_id', $ref->id)->exists();
        if ($exists) {
            return back()->with('status', 'この募集には既に申込済みです。');
        }

        CompetitionApplication::create([
            'competition_id' => $competition->id,
            'nomination_id'  => $nom->id,
            'referee_id'     => $ref->id,
            'status'         => 'applied',
        ]);

        return back()->with('status', '申込を受け付けました。');
    }
}
