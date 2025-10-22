<?php

// app/Http/Controllers/Admin/RefereeImportController.php
namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RefereeImportRequest;

class RefereeImportController extends Controller
{
    public function show()
    {
        return view('admin.referees.import');
    }

    public function store(RefereeImportRequest $request)
    {
        $allowedKeys = ['registration_number', 'kanji_set', 'kana_set', 'roman_set'];
        $uniqueKey   = $request->input('unique_key', 'registration_number');

        $request->validate([
            'unique_key' => ['required', Rule::in($allowedKeys)],
            'encoding'   => ['nullable', Rule::in(['utf8','sjis'])],
            'csv'        => ['required','file','mimetypes:text/plain,text/csv','mimes:csv,txt'],
        ]);
        
        $file = $request->file('csv');

        if (!$file->isValid()) {
            return back()->withErrors(['csv' => 'アップロードに失敗しました。']);
        }

        $tmpPath  = $file->getRealPath(); // ← これなら storage/app を作らなくてOK
        $encoding = $request->input('encoding', 'utf8');

        if ($encoding === 'sjis') {
            $utf8 = mb_convert_encoding(file_get_contents($tmpPath), 'UTF-8', 'SJIS-win');
            $tmpPath = storage_path('app/tmp/'.\Illuminate\Support\Str::uuid().'.csv');
            \Illuminate\Support\Facades\Storage::put('tmp/'.basename($tmpPath), $utf8);
        }

        if (($handle = fopen($tmpPath, 'r')) === false) {
            return back()->withErrors(['csv' => 'CSVファイルを開けませんでした。']);
        }
        
        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return back()->withErrors(['csv' => 'ヘッダー行が読み取れません。']);
        }

        $requiredColsByKey = [
            'registration_number' => ['surname','name','registration_number'],
            'kanji_set'           => ['surname_kanji','name_kanji'],
            'kana_set'            => ['surname_kana','name_kana'],
            'roman_set'           => ['surname','name'],
        ];

            $requiredCols = $requiredColsByKey[$uniqueKey] ?? ['registration_number'];

            $index = [];
            foreach ($header as $i => $col) {
                $index[Str::snake(trim($col))] = $i;
            }

            foreach ($requiredCols as $col) {
                if (!isset($index[$col])) {
                    fclose($handle);
                    return back()->withErrors(['csv' => "CSVに「{$col}」列がありません。"]);
                }
            }
        $requiredCols = $requiredColsByKey[$uniqueKey] ?? ['registration_number'];

        $index = [];
        foreach ($header as $i => $col) {
            $index[Str::snake(trim($col))] = $i;
        }

        foreach ($requiredCols as $col) {
            if (!isset($index[$col])) {
                fclose($handle);
                return back()->withErrors(['csv' => "CSVに「{$col}」列がありません。"]);
            }
        }

        $map = [
            'user_id'             => 'user_id',
            'surname'             => 'surname',
            'name'                => 'name',
            'surname_kanji'       => 'surname_kanji',
            'name_kanji'          => 'name_kanji',
            'surname_kana'        => 'surname_kana',
            'name_kana'           => 'name_kana',
            'registration_number' => 'registration_number',
            'prefecture_id'       => 'prefecture_id',
            'organization_id'     => 'organization_id',
            'license_id'          => 'license_id',
            'birth_date'          => 'birth_date',
            'mrs_member_id'       => 'mrs_member_id',
            'remarks'             => 'remarks',
        ];

        $now   = now();
        $rows  = [];
        $count = 0;
        $chunk = 500;

        // 行読み込み
        while (($data = fgetcsv($handle)) !== false) {
            $row = [];
            foreach ($map as $csvKey => $dbCol) {
                $v = isset($index[$csvKey]) ? ($data[$index[$csvKey]] ?? null) : null;
                $v = (is_string($v) && trim($v)==='') ? null : $v;

                // 日付の整形（birth_date は Y-m-d に寄せる）
                if ($dbCol === 'birth_date' && $v) {
                    $v = $this->normalizeDate($v); // 失敗時は null
                }

                // 数値FKは空文字なら null
                if (in_array($dbCol, ['user_id','prefecture_id','organization_id','license_id'], true)) {
                    $v = $v === null ? null : (is_numeric($v) ? (int)$v : null);
                }

                $row[$dbCol] = $v;
            }

            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $row['deleted_at'] = null;

            $rows[] = $row;
            $count++;

            if (count($rows) === $chunk) {
                $this->flush($rows, $uniqueKey);
                $rows = [];
            }
        }

        fclose($handle);

        if ($encoding === 'sjis') {
            Storage::delete('tmp/'.basename($tmpPath));
        }

        if ($rows) {
            $this->flush($rows, $uniqueKey);
        }
        return back()->with('status', "CSVを取り込みました（{$count} 行）。");
    }

    private function normalizeDate($v): ?string
    {
        // 1999/1/2 や 1999-01-02 を Y-m-d に
        $v = str_replace('/', '-', trim($v));
        try {
            return Carbon::parse($v)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function flush(array $rows, string $uniqueKey): void
    {
        $conflict = match ($uniqueKey) {
            'registration_number' => ['registration_number'],
            'kanji_set'           => ['surname_kanji','name_kanji'],
            'kana_set'            => ['surname_kana','name_kana'],
            'roman_set'           => ['surname','name'],
            default               => ['registration_number'],
        };

        [$ok, $skip] = collect($rows)->partition(function ($r) use ($conflict) {
            foreach ($conflict as $col) {
                if (empty($r[$col])) return false;
            }
            return true;
        });

        if ($ok->isNotEmpty()) {
            DB::table('referees')->upsert(
                $ok->all(),
                $conflict,
                [
                    'user_id','surname','name','surname_kanji','name_kanji',
                    'surname_kana','name_kana','prefecture_id','organization_id',
                    'license_id','birth_date','mrs_member_id','remarks',
                    'updated_at','deleted_at',
                ]
            );
        }

        if ($skip->isNotEmpty()) {
            Log::warning('Referee import skipped rows (missing conflict columns)', [
                'uniqueKey' => $uniqueKey,
                'rows' => $skip->count(),
            ]);
        }
    }

    public function database()
    {
        return view('admin.referees.database');
    }
    
}

