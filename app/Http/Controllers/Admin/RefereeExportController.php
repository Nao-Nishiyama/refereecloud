<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Organization;
use App\Models\Referee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefereeExportController extends Controller
{

    public function show(Request $request)
    {
        $licenseIds      = (array)$request->input('license_ids', []);
        $organizationIds = (array)$request->input('organization_ids', []);

        $licenses      = License::orderBy('id')->get(['id','name']);
        $organizations = Organization::orderBy('id')->get(['id','short_name']);

        return view('admin.referees.export', compact(
            'licenses', 'organizations', 'licenseIds', 'organizationIds'
        ));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $validated = $request->validate([
            'license_ids' => ['nullable', 'array'],
            'license_ids.*' => ['integer', 'distinct', 'exists:licenses,id'],
            'organization_ids' => ['nullable', 'array'],
            'organization_ids.*' => ['integer', 'distinct', 'exists:organizations,id'],
        ]);

        $query = Referee::query();
        
        if (!empty($validated['license_ids'])) {
            $query->whereIn('referees.license_id', $validated['license_ids']);
        }

        if (!empty($validated['organization_ids'])) {
            $query->whereIn('referees.organization_id', $validated['organization_ids']);
        }

        $currentLicenseYears = DB::table('referee_license_histories')
            ->selectRaw('referee_id, license_id, MIN(acquired_year) as current_license_year')
            ->groupBy('referee_id', 'license_id');

        $referees = $query
            ->leftJoinSub($currentLicenseYears, 'cly', function ($join) {
                $join->on('cly.referee_id', '=', 'referees.id')
                    ->on('cly.license_id', '=', 'referees.license_id');
            })
            ->with([
                'license:id,name',
                'organization:id,short_name',
                'licenseHistories:id,referee_id,license_id,acquired_year',
            ])
            ->orderBy('referees.license_id', 'asc')
            ->orderBy('cly.current_license_year', 'asc')
            ->orderBy('referees.registration_number', 'asc')
            ->get([
                'referees.id',
                'referees.registration_number',
                'referees.surname_kanji',
                'referees.name_kanji',
                'referees.surname_kana',
                'referees.name_kana',
                'referees.surname',
                'referees.name',
                'referees.birth_date',
                'referees.email',
                'referees.license_id',
                'referees.organization_id',
            ]);

        $filename = 'referees_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($referees) {
            $out = fopen('php://output', 'w');

            $toSjis = fn(array $row) => array_map(
                fn($v) => mb_convert_encoding((string)$v, 'SJIS-win', 'UTF-8'),
                $row
            );

            fputcsv($out, $toSjis([
                '登録番号','氏名','フリガナ','英字姓','英字名','生年月日','メール','資格','取得年','団体'
            ]));

            foreach ($referees as $r) {
                fputcsv($out, $toSjis([
                    $r->registration_number,
                    $r->surname_kanji.' '.$r->name_kanji,
                    $r->surname_kana.' '.$r->name_kana,
                    $r->surname,
                    $r->name,
                    (string)$r->birth_date,
                    $r->email,
                    optional($r->license)->name ?? '',
                    $r->current_license_year ?? '',
                    optional($r->organization)->short_name ?? '',
                ]));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
        ]);
    }
}