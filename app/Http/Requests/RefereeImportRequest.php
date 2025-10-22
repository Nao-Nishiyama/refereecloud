<?php

// app/Http/Requests/RefereeImportRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefereeImportRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'csv' => [
                'required','file','max:10240', // 10MB
                'mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel'
            ],
            // CSVがShift_JISのときONにするチェックボックス（任意）
            'encoding' => ['nullable','in:utf8,sjis'],
            // アップサートのユニークキー（email or registration_number）
            'unique_key' => ['nullable','in:email,registration_number'],
        ];
    }
    public function messages(): array
    {
        return [
            'csv.required' => 'CSVファイルを選択してください。',
            'csv.mimetypes' => 'CSVファイルをアップロードしてください。',
        ];
    }
}
