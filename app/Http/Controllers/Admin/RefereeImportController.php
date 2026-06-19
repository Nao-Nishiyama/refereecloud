<?php

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
        $uniqueKey = $request->input('unique_key', 'registration_number');

        $request->validate([
            'unique_key' => ['required', Rule::in($allowedKeys)],
            'encoding' => ['nullable', Rule::in(['utf8', 'sjis'])],
            'csv' => ['required', 'file', 'mimetypes:text/plain,text/csv', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv');

        if (!$file->isValid()) {
            return back()->withErrors(['csv' => 'アップロードに失敗しました。']);
        }

        $tmpPath = $file->getRealPath();
        $encoding = $request->input('encoding', 'utf8');

        if ($encoding === 'sjis') {
            $utf8 = mb_convert_encoding(file_get_contents($tmpPath), 'UTF-8', 'SJIS-win');
            $tmpPath = storage_path('app/tmp/' . Str::uuid() . '.csv');
            Storage::put('tmp/' . basename($tmpPath), $utf8);
        }

        if (($handle = fopen($tmpPath, 'r')) === false) {
            return back()->withErrors(['csv' => 'CSVファイルを開けませんでした。']);
        }

        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return back()->withErrors(['csv' => 'ヘッダー行が読み取れません。']);
        }

        $index = $this->makeHeaderIndex($header);

        $requiredColsByKey = [
            'registration_number' => ['surname', 'name', 'registration_number'],
            'kanji_set' => ['surname_kanji', 'name_kanji'],
            'kana_set' => ['surname_kana', 'name_kana'],
            'roman_set' => ['surname', 'name'],
        ];

        $requiredCols = $requiredColsByKey[$uniqueKey] ?? ['registration_number'];

        foreach ($requiredCols as $col) {
            if (!isset($index[$col])) {
                fclose($handle);
                return back()->withErrors(['csv' => "CSVに「{$col}」列がありません。"]);
            }
        }

        $map = [
            'user_id' => 'user_id',
            'surname' => 'surname',
            'name' => 'name',
            'surname_kanji' => 'surname_kanji',
            'name_kanji' => 'name_kanji',
            'surname_kana' => 'surname_kana',
            'name_kana' => 'name_kana',
            'registration_number' => 'registration_number',
            'email' => 'email',
            'prefecture_id' => 'prefecture_id',
            'organization_id' => 'organization_id',
            'license_id' => 'license_id',
            'birth_date' => 'birth_date',
            'gender' => 'gender',
            'mrs_member_id' => 'mrs_member_id',
            'remarks' => 'remarks',
        ];

        $now = now();
        $rows = [];
        $count = 0;
        $chunk = 500;

        while (($data = fgetcsv($handle)) !== false) {
            if ($this->isEmptyCsvRow($data)) {
                continue;
            }

            $row = [];

            foreach ($map as $csvKey => $dbCol) {
                $v = isset($index[$csvKey]) ? ($data[$index[$csvKey]] ?? null) : null;
                $v = is_string($v) ? trim($v) : $v;
                $v = ($v === '') ? null : $v;

                if ($dbCol === 'birth_date' && $v) {
                    $v = $this->normalizeDate($v);
                }

                if (in_array($dbCol, ['user_id', 'prefecture_id', 'organization_id', 'license_id', 'gender'], true)) {
                    $v = $v === null ? null : (is_numeric($v) ? (int) $v : null);
                }

                $row[$dbCol] = $v;
            }

            $row['_license_acquired_year'] = $this->readLicenseAcquiredYear($data, $index);

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
            Storage::delete('tmp/' . basename($tmpPath));
        }

        if ($rows) {
            $this->flush($rows, $uniqueKey);
        }

        return back()->with('status', "CSVを取り込みました（{$count} 行）。");
    }

    private function makeHeaderIndex(array $header): array
    {
        $index = [];

        foreach ($header as $i => $col) {
            $col = (string) $col;

            // UTF-8 BOM除去
            $col = preg_replace('/^\xEF\xBB\xBF/', '', $col);

            $key = Str::snake(trim($col));

            // 末尾の空カラムなどは無視
            if ($key === '') {
                continue;
            }

            $index[$key] = $i;
        }

        return $index;
    }

    private function isEmptyCsvRow(array $data): bool
    {
        foreach ($data as $v) {
            if (trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }

    private function readLicenseAcquiredYear(array $data, array $index): ?int
    {
        foreach (['license_acquired_year', 'acquired_year', 'current_license_year'] as $key) {
            if (!isset($index[$key])) {
                continue;
            }

            $value = $data[$index[$key]] ?? null;
            $value = is_string($value) ? trim($value) : $value;

            if ($value !== '' && is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    private function normalizeDate($v): ?string
    {
        $v = str_replace('/', '-', trim((string) $v));

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
            'kanji_set' => ['surname_kanji', 'name_kanji'],
            'kana_set' => ['surname_kana', 'name_kana'],
            'roman_set' => ['surname', 'name'],
            default => ['registration_number'],
        };

        [$ok, $skip] = collect($rows)->partition(function ($r) use ($conflict) {
            foreach ($conflict as $col) {
                if (empty($r[$col])) {
                    return false;
                }
            }

            return true;
        });

        if ($ok->isNotEmpty()) {
            DB::transaction(function () use ($ok, $conflict) {
                $refereeRows = $ok->map(function ($r) {
                    unset($r['_license_acquired_year']);
                    return $r;
                })->all();

                DB::table('referees')->upsert(
                    $refereeRows,
                    $conflict,
                    [
                        'user_id',
                        'surname',
                        'name',
                        'surname_kanji',
                        'name_kanji',
                        'surname_kana',
                        'name_kana',
                        'email',
                        'prefecture_id',
                        'organization_id',
                        'license_id',
                        'birth_date',
                        'gender',
                        'mrs_member_id',
                        'remarks',
                        'updated_at',
                        'deleted_at',
                    ]
                );

                foreach ($ok as $r) {
                    if (empty($r['license_id']) || empty($r['_license_acquired_year'])) {
                        continue;
                    }

                    $refereeQuery = DB::table('referees');

                    foreach ($conflict as $col) {
                        $refereeQuery->where($col, $r[$col]);
                    }

                    $referee = $refereeQuery->first(['id']);

                    if (!$referee) {
                        continue;
                    }

                    DB::table('referee_license_histories')->updateOrInsert(
                        [
                            'referee_id' => $referee->id,
                            'license_id' => $r['license_id'],
                            'acquired_year' => $r['_license_acquired_year'],
                        ],
                        [
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            });
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

    public function template(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'referee_import_template.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            $toSjis = fn(array $row) => array_map(
                fn($v) => mb_convert_encoding((string) $v, 'SJIS-win', 'UTF-8'),
                $row
            );

            fputcsv($out, $toSjis([
                'registration_number',
                'surname_kanji',
                'name_kanji',
                'surname_kana',
                'name_kana',
                'surname',
                'name',
                'email',
                'prefecture_id',
                'organization_id',
                'license_id',
                'license_acquired_year',
                'birth_date',
                'gender',
                'mrs_member_id',
                'remarks',
            ]));

            fputcsv($out, $toSjis([
                'A2601',
                '京都',
                '太郎',
                'キョウト',
                'タロウ',
                'Kyoto',
                'Tarou',
                'sample@example.com',
                '26',
                '1',
                '5',
                '2026',
                '1980-01-01',
                '1',
                'JVA000000000',
                'CSV取込サンプル',
            ]));

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
        ]);
    }
}