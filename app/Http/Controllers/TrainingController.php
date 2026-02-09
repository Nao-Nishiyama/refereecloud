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

        return view('trainings.index', compact('trainings'));
    }

    public function show($id)
    {
        $training = Training::with(['files' => fn($q) => $q->latest()])->findOrFail($id);

        abort_unless($training->is_published, 404);

        return view('trainings.show', compact('training'));
    }
}
