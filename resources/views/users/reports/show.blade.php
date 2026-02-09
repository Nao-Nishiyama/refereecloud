@extends('layouts.app')

@section('title', 'Annual Reports')
    

@section('content')
    <style>
    .custom-hover:hover {
        background-color: #747474;
        color: #e6e6e6;
        transition: all 0.2s ease-in-out;
    }
    </style>
<div class="container">
    @if ($user->id === Auth::user()->id)
        <div class="row gx-5 d-flex justify-content-center mb-3">
            <div class="col-auto">
                <a class="btn border border-secondary shadow rounded custom-hover" href="{{ route('reports.create') }}">{{ __('活動を報告する') }}</a>
            </div>
        </div>
    @endif
        @if ($user->referee)
        <td>
            {{ $user->referee->surname_kanji }} {{ $user->referee->name_kanji }}
        @else
            {{ $user->surname_kana }} {{ $user->name_kana}}
        <span class="text-muted" style="font-size: .75em";>：データ未連携</span>
        </td>
        @endif
        <div class="row gx-5 justify-content-center">
        <div class="col-12">
            <div class="table-responsive">
            <table class="table table-hover align-middle bg-white border text-secondary text-center">
                <thead class="small table-success text-secondary">
                <tr>
                    <th>報告年度</th>
                    <th>主審<br><span style="font-size: .75em">（ブロック以上）</span></th>
                    <th>副審<br><span style="font-size: .75em">（ブロック以上）</span></th>
                    <th>主審<br><span style="font-size: .75em">（都道府県）</span></th>
                    <th>副審<br><span style="font-size: .75em">（都道府県）</span></th>
                    <th>記録</th>
                    <th>AS</th>
                    <th>線審</th>
                    <th>講習会</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->year }}</td>
                    <td>{{ $report->first_ref_block }}</td>
                    <td>{{ $report->second_ref_block }}</td>
                    <td>{{ $report->first_ref }}</td>
                    <td>{{ $report->second_ref }}</td>
                    <td>{{ $report->scorer }}</td>
                    <td>{{ $report->assistant_scorer }}</td>
                    <td>{{ $report->linejudge }}</td>
                    <td>{{ $report->training }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>

        @if (Auth::user()->role_id === 1 | Auth::user()->role_id === 2 | Auth::user()->role_id === 3)
            <div class="text-center mt-3">
            <a href="{{ route('admin.referees.reports.show') }}" class="btn btn-dark px-4 text-nowrap">一覧へ戻る</a>
            </div>
        @endif
            <div class="text-center mt-3">
            <a href="{{ route('index') }}" class="btn btn-outline-dark px-4 text-nowrap">ダッシュボードへ戻る</a>
            </div>
        </div>
    </div>
@endsection
