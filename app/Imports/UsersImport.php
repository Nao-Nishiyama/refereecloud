<?php

// app/Imports/UsersImport.php
namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class UsersImport implements ToModel, WithHeadingRow, WithUpserts, WithValidation, WithChunkReading, SkipsOnError
{
    use SkipsErrors;

    /** Excelの見出し行は1行目（Googleシートのシート3ヘッダ準拠） */
    public function headingRow(): int { return 1; }

    /** upsert の一意キー（登録番号） */
    public function uniqueBy()
    {
        return 'registration_number';
    }

    /** 既存レコードで更新するカラム（password は除外） */
    public function upsertColumns(): array
    {
        return [
            'email',
            'surname', 'name',
            'surname_kanji','name_kanji',
            'surname_kana','name_kana',
            'prefecture_id','organization_id','license_id',
            'birth_date',
            'mrs_member_id',
            'role_id',
            'remarks',
        ];
    }

    /** バリデーション（最低限） */
    public function rules(): array
    {
        return [
            'registration_number' => ['nullable','string','max:50'],
            'surname_kanji'       => ['nullable','string','max:50'],
            'name_kanji'          => ['nullable','string','max:50'],
            'surname_kana'        => ['nullable','string','max:50'],
            'name_kana'           => ['nullable','string','max:50'],
            'prefecture_id'       => ['nullable','integer'],
            'organization_id'     => ['nullable','integer'],
            'license_id'          => ['nullable','integer'],
            'mrs_member_id'       => ['nullable','string','max:50'],
            'role_id'             => ['nullable','integer'], // 無ければ後で既定化
            'email'               => ['nullable','email','max:255'],
            'birth_date'          => ['nullable','date'],
        ];
    }

    /** 大量行でも安定 */
    public function chunkSize(): int { return 1000; }

    public function model(array $row)
    {
        // position_id の既定（Excelの列が無い/空なら 4=User を採用）
        $positionId = isset($row['position_id']) && is_numeric($row['position_id'])
            ? (int)$row['position_id'] : 4;

        // ランダム平文（Userモデルに casts['password'=>'hashed'] がある想定）
        $plain = method_exists(Str::class, 'password')
            ? Str::password(12)
            : Str::random(16);

        // ★ ここで password をセットしても、
        // upsertColumns() に含めていないため、
        // 既存レコードでは password は更新されません（新規のみ反映）
        return new User([
            'surname'             => $row['surname']           ?? null,  // ある場合のみ
            'name'                => $row['name']              ?? null,
            'email'               => $row['email']             ?? null,
            'password'            => $plain,

            'surname_kanji'       => $row['surname_kanji']     ?? null,
            'name_kanji'          => $row['name_kanji']        ?? null,
            'surname_kana'        => $row['surname_kana']      ?? null,
            'name_kana'           => $row['name_kana']         ?? null,

            'registration_number' => $row['registration_number'] ?? null, // 例: B0101
            'prefecture_id'       => $row['prefecture_id']     ?? null,
            'organization_id'     => $row['organization_id']   ?? null,
            'license_id'          => $row['license_id']        ?? null,
            'birth_date'          => $row['birth_date']        ?? null,   // 文字列なら後でcast

            'mrs_member_id'       => $row['mrs_member_id']     ?? null,
            'role_id'         => $positionId,
            'remarks'             => $row['remarks']           ?? null,
        ]);
    }
}
