<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class AnnualReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
    return [
        'year' => [
            'required',
            'digits:4',
            Rule::unique('annual_reports')->where(function ($query) {
                return $query->where('user_id', Auth::id());
            }),
        ],
        // 他のルールもここに追加
            'first_ref_block' => 'required|numeric|min:0',
            'second_ref_block' => 'required|numeric|min:0',
            'first_ref' => 'required|numeric|min:0',
            'second_ref' => 'required|numeric|min:0',
            'scorer' => 'required|numeric|min:0',
            'assistant_scorer' => 'required|numeric|min:0',
            'linejudge' => 'required|numeric|min:0',
            'training' => 'required|numeric|min:0'
        ];
    }

    public function messages()
    {
        return [
            'year.unique' => 'すでに登録されています',
            'year.required' => '年度は必須です。',
            'year.digits'   => '年度は4桁の数字で入力してください。',
            'required' => '必須項目です。実績のない場合は、0を入力してください。'
        ];
    }

}
