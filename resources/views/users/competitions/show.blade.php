@extends('layouts.app')

@section('title', '大会')
    

@section('content')
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        {{-- Competition List --}}
        <table class="table align-middle table-hover bg-white border text secondary w-75 text-center shadow">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>大会名</th>
                    <th>種別</th>
                    <th>開催地</th>
                    <th>会場</th>
                    <th>募集日程</th>
                    <th>締切</th>
                    <th>状況</th>
                    <th>申込</th>
                </tr>
            </thead>
            <tbody>
                @if ( Auth::user()->isNominated() )
                    @foreach ($all_competitions as $competition)
                    <tr>
                        <td>{{ $competition->name }}</td>
                        <td>{{ $competition->type->name }}</td>
                        <td>{{ $competition->city }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($competition->venue, 5, '') }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
                            〜
                            {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}
                        </td>
                        <td>{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}</td>
                        <td>
                            @php
                            $ref = Auth::user()->referee;

                            // ライセンス
                            $refereeLicenseId   = optional($ref)->license_id;
                            $allowedLicenseIds  = $competition->competitionLicense->pluck('license_id')->filter()->unique();

                            $refereeCategoryIds = $ref ? $ref->categories->pluck('category_id') : collect();

                            $allowedCategoryIds = $competition->competitionLicense->pluck('category_id')->filter()->unique();

                            $licenseOk  = $refereeLicenseId && $allowedLicenseIds->contains($refereeLicenseId);
                            $categoryOk = $refereeCategoryIds->intersect($allowedCategoryIds)->isNotEmpty();
                            @endphp

                            @if ($licenseOk || $categoryOk)
                            <span class="text-success">対象</span>
                            @else
                            <span class="text-danger">対象外</span>
                            @endif

                        </td>
                        <td><a href="{{ route('competition.apply', $competition->id) }}">申し込み</a></td>
                    </tr>                    
                    @endforeach
                    @else
                    <tr><td colspan="8">募集なし</td></tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
