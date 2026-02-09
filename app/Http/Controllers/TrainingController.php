<?php

namespace App\Http\Controllers;

use App\Models\Training;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::query()
            ->where('is_published', true)
            ->orderByRaw('COALESCE(event_date, created_at) DESC')
            ->paginate(20);

        $trainingsWithTrashed = Training::query()
            ->where('is_published', 1)
            ->with([
                'prefecture:id,name',
                'organization:id,short_name',
                'files' => fn($q) => $q->latest()->limit(1),
            ])
            ->withTrashed()
            ->orderByDesc('event_date')
            ->orderByDesc('id')
            ->get();

        return view('trainings.index', compact('trainings', 'trainingsWithTrashed'));
    }

    public function show($id)
    {
        $training = Training::with([
        'prefecture:id,name',
        'organization:id,short_name',
        'files' => fn($q) => $q->latest(),
        'files.uploader:id', // uploaderリレーションあるなら
        ])->findOrFail($id);

        abort_unless($training->is_published, 404);

        return view('trainings.show', compact('training'));
    }
}
