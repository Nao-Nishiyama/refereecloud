@extends('layouts.app')

@section('title', '大会リスト')
    
@section('content')
<div class="container">
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        {{-- Competition List --}}
        <table class="table align-middle table-hover bg-white border text-secondary text-center shadow w-75">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>大会名</th>
                    <th>会場</th>
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
                            <td>{{ \Illuminate\Support\Str::limit($competition->name, 20) }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($competition->venue, 12) }}</td>
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