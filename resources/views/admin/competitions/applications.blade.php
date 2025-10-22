@extends('layouts.app')

@section('title', '大会リスト')
    
@section('content')
<div class="container">
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        {{-- Competition List --}}
        <table class="table align-middle table-hover bg-white border text-secondary text-center shadow">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>大会名</th>
                   <th>種別</th>
                    <th>開催地</th>
                    <th>会場</th>
                    <th>募集日程</th>
                    <th>締切</th>
                    <th>申込数</th>
                </tr>
            </thead>
            <tbody>
                @if (Auth::user()->role_id === 1)
                    @php
                        $competitions = $all;
                    @endphp
                @endif
                @if($competitions)
                    @foreach ($comps as $competition)
                        <tr>
                            <td>{{ $competition->name }}</td>
                            <td>{{ $competition->type->name }}</td>
                            <td>{{ $competition->city }}</td>
                            <td>{{ $competition->venue }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
                                〜
                                {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}</td>
                            <td>
                                <a href="{{route('admin.applications.detail', $competition->id)}}" class="btn btn-outline-dark btn-sm text-decoration-none fw-bold">{{ $competition->applicants_count }}</a>
                            </td>
                        </tr>                    
                    @endforeach
                @else                
                    <tr><td colspan="8">大会なし</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection