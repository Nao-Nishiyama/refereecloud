<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class CompetitionImportController extends Controller
{
    public function show()
    {
        return view('admin.competitions.import');
    }

    public function store(Request $request)
    {
        $allowedKeys = ['name_date'];
        $uniqueKey   = $request->input('unique_key', 'name_date');

        $request->validate([
            'unique_key' => ['required', Rule::in(['name_date'])], // ← 追加
            'encoding'   => ['nullable', Rule::in(['utf8','sjis'])],
            'csv'        => ['required','file','mimetypes:text/plain,text/csv','mimes:csv,txt'],
        ]);


        $file = $request->file('csv');
        if (!$file->isValid()) {
            return back()->withErrors(['csv' => 'アップロードに失敗しました。']);
        }

        // 一時パス
        $tmpPath  = $file->getRealPath();
        $encoding = $request->input('encoding', 'utf8');

        // 文字コード変換（必要なら）
        if ($encoding === 'sjis') {
            $utf8   = mb_convert_encoding(file_get_contents($tmpPath), 'UTF-8', 'SJIS-win');
            $tmpPath = storage_path('app/tmp/'.Str::uuid().'.csv');
            Storage::put('tmp/'.basename($tmpPath), $utf8);
        }

        // 開く
        if (($handle = fopen($tmpPath, 'r')) === false) {
            return back()->withErrors(['csv' => 'CSVファイルを開けませんでした。']);
        }

        // ヘッダ
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return back()->withErrors(['csv' => 'ヘッダー行が読み取れません。']);
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0] ?? '');

        $index = [];
        foreach ($header as $i => $col) {
            $index[Str::snake(trim($col))] = $i;
        }

        $map = [
            'name'                 => 'name',
            'type_id'              => 'type_id',
            'city'                 => 'city',
            'venue'                => 'venue',
            'start_day'            => 'start_day',
            'end_day'              => 'end_day',
            'application_deadline' => 'application_deadline',
            'organizer_message'    => 'organizer_message',
            'admin_private_note'    => 'admin_private_note',
        ];

        $requiredColsByKey = [
            'name_date'   => ['name','start_day'],
        ];
        $requiredCols = $requiredColsByKey[$uniqueKey];

        foreach ($requiredCols as $col) {
            if (!isset($index[$col])) {
                fclose($handle);
                if ($encoding === 'sjis') Storage::delete('tmp/'.basename($tmpPath));
                return back()->withErrors(['csv' => "CSVに「{$col}」列がありません。"]);
            }
        }

        $now   = now();
        $rows  = [];
        $count = 0;
        $chunk = 500;

        while (($data = fgetcsv($handle)) !== false) {
            $row = [];

            foreach ($map as $csvKey => $dbCol) {
                $v = isset($index[$csvKey]) ? ($data[$index[$csvKey]] ?? null) : null;
                $v = (is_string($v) && trim($v)==='') ? null : $v;

                if (in_array($dbCol, ['start_day','end_day','application_deadline'], true) && $v) {
                    $v = $this->normalizeDate($v);
                }

                if (in_array($dbCol, ['type_id'], true)) {
                    $v = $v === null ? null : (is_numeric($v) ? (int)$v : null);
                }

                $row[$dbCol] = $v;
            }

            $row['created_at'] = $now;
            $row['updated_at'] = $now;

            $rows[] = $row;
            $count++;

            if (count($rows) === $chunk) {
                $this->flush($rows, $uniqueKey);
                $rows = [];
            }
        }
        fclose($handle);
        if ($encoding === 'sjis') Storage::delete('tmp/'.basename($tmpPath));

        if ($rows) {
            $this->flush($rows, $uniqueKey);
        }

        return back()->with('status', "CSVを取り込みました（{$count} 行）。");
    }

    private function normalizeDate($v): ?string
    {
        $v = str_replace('/', '-', trim($v));
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function flush(array $rows, string $uniqueKey): void
    {
        // upsert 衝突キー（★ competitions テーブルへ）
        $conflict = match ($uniqueKey) {
            'external_id' => ['external_id'],
            'name_date'   => ['name','start_day'],
            default       => ['name','start_day'],
        };

        // 衝突キーが null の行はスキップ
        [$ok, $skip] = collect($rows)->partition(function ($r) use ($conflict) {
            foreach ($conflict as $col) {
                if (empty($r[$col])) return false;
            }
            return true;
        });

        if ($ok->isNotEmpty()) {
            DB::table('competitions')->upsert(
                $ok->all(),
                $conflict,
                [
                    'type_id','city','venue','end_day',
                    'application_deadline','organizer_message','admin_private_note', 'updated_at'
                ]
            );
        }

        if ($skip->isNotEmpty()) {
            Log::warning('Competition import skipped rows (missing conflict columns)', [
                'uniqueKey' => $uniqueKey,
                'rows'      => $skip->count(),
            ]);
        }
    }
}
