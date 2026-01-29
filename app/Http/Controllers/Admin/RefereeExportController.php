<?php

namespace App\Http\Controllers\Admin;

use App\Models\License;
use App\Models\Referee;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
            $query->whereIn('license_id', $validated['license_ids']);
        }
        if (!empty($validated['organization_ids'])) {
            $query->whereIn('organization_id', $validated['organization_ids']);
        }

        $referees = $query
            ->with(['license:id,name', 'organization:id,short_name'])
            ->orderBy('registration_number')
            ->get([
                'id',
                'registration_number',
                'surname_kanji','name_kanji',
                'surname_kana','name_kana',
                'surname','name',
                'birth_date',
                'email',
                'license_id','organization_id',
            ]);

        $filename = 'referees_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($referees) {
            $out = fopen('php://output', 'w');

            $toSjis = fn(array $row) => array_map(
                fn($v) => mb_convert_encoding((string)$v, 'SJIS-win', 'UTF-8'),
                $row
            );

            fputcsv($out, $toSjis([
                '登録番号','氏名','フリガナ','英字姓','英字名','生年月日','メール','資格','団体'
            ]));

            foreach ($referees as $r) {
                fputcsv($out, $toSjis([
                    $r->registration_number,
                    $r->surname_kanji.' '.$r->name_kanji,
                    $r->surname_kana.' '.$r->name_kana,
                    $r->surname,
                    $r->name,
                    (string)$r->birth_date, // CarbonでもOK
                    $r->email,
                    optional($r->license)->name ?? '',
                    optional($r->organization)->short_name ?? '',
                ]));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
        ]);
    }
}