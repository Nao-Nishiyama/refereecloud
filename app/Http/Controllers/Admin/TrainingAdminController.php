<?php

namespace App\Http\Controllers\Admin;

use App\Models\Training;
use App\Models\Prefecture;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TrainingAdminController extends Controller
{
    public function __construct()
    {
        // admin/committeeのみ
        $this->middleware(function ($request, $next) {
            abort_unless(Gate::allows('manage-trainings'), 403);
            return $next($request);
        });
    }

    public function index()
    {
        $trainings = Training::query()
            ->with(['files' => fn($q) => $q->latest()->limit(1)]) // 最新1件だけ読む
            ->orderByDesc('event_date')
            ->get();

        $trainingsWithTrashed = Training::query()
            ->withTrashed()
            ->orderByDesc('event_date')
            ->get();

        return view('admin.trainings.index', compact('trainings', 'trainingsWithTrashed'));
    }

    public function create()
    {
        return view('admin.trainings.create', [
            'prefectures'   => Prefecture::orderBy('id')->get(['id','name']),
            'organizations' => Organization::orderBy('id')->get(['id','short_name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'event_date' => ['nullable','date'],
            'venue' => ['nullable','string','max:255'],
            'summary' => ['nullable','string'],
            'body' => ['nullable','string'],
            'deadline' => ['nullable','date'],
            'is_published' => ['nullable','boolean'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $data['is_published'] = (bool)($data['is_published'] ?? false);
        $data['created_by'] = Auth::user()->id;

        $training = Training::create($data);

        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');

            $path = $file->store('trainings', 'public');

            // 履歴に積む
            $training->files()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => Auth::user()->id,
            ]);
        }

        return redirect()->route('admin.trainings.index')->with('status','講習会案内を作成しました。');
    }

    public function edit(Training $training)
    {
        $training->load(['files.uploader']);

        return view('admin.trainings.edit', [
            'training'      => $training,
            'prefectures'   => Prefecture::orderBy('id')->get(['id','name']),
            'organizations' => Organization::orderBy('id')->get(['id','short_name']),
        ]);
    }

    public function update(Request $request, Training $training)
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'event_date' => ['nullable','date'],
            'venue' => ['nullable','string','max:255'],
            'summary' => ['nullable','string'],
            'body' => ['nullable','string'],
            'deadline' => ['nullable','date'],
            'is_published' => ['nullable','boolean'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $data['is_published'] = (bool)($data['is_published'] ?? false);

        $training->update($data);

        if ($request->hasFile('pdf')) {
            $file = $request->file('pdf');

            $path = $file->store('trainings', 'public');

            // 履歴に積む
            $training->files()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'uploaded_by'   => Auth::user()->id,
            ]);
        }

        return redirect()->route('admin.trainings.index')->with('status','更新しました。');
    }

    public function destroy(Training $training)
    {
        $training->delete();
        return back()->with('status','削除しました。');
    }
}
