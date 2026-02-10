<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function index()
    {
    $user = Auth::user();

    $isAdmin = ((int)$user->role_id === 1);
    $isCommittee = ((int)$user->role_id === 2);
    $isChief = ((int)$user->role_id === 3);

    $myOrgId = (int) optional($user->referee)->organization_id;
    $myPrefId = (int) optional($user->referee)->prefecture_id;

    $trainings = Training::query()
        ->when(!$isAdmin && !$isCommittee && !$isChief, function ($q) use($myOrgId, $myPrefId) {
            $q->where('is_published', true)
            ->where('organization_id', $myOrgId)
            ->where('prefecture_id', $myPrefId)
            ->orWhereNull('organization_id')
            ->orWhereNull('prefecture_id');
        })
        ->when($isChief && !$isAdmin && !$isCommittee, function ($q) use ($myOrgId, $myPrefId) {
            $q->where('organization_id', $myOrgId)->orWhereNull('organization_id')->where('prefecture_id', $myPrefId)->orWhereNull('prefecture_id');
        })
        ->when($isCommittee && !$isAdmin && !$isChief, function ($q) use ($myPrefId) {
            $q->withTrashed()->where('prefecture_id', $myPrefId)->orWhereNull('prefecture_id');
        })
        ->when($isAdmin && !$isCommittee && !$isChief, function ($q){
            $q->withTrashed();
        })
        ->orderByRaw('COALESCE(event_date, created_at) DESC')
        ->paginate(20)
        ->withQueryString();

        return view('trainings.index', compact('trainings'));
    }

    public function show($id)
    {
        $training = Training::with([
        'prefecture:id,name',
        'organization:id,full_name',
        'files' => fn($q) => $q->latest(),
        'files.uploader:id', // uploaderリレーションあるなら
        ])->findOrFail($id);

        abort_unless($training->is_published, 404);

        return view('trainings.show', compact('training'));
    }
}
