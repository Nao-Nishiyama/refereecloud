@extends('layouts.app')

@section('title', '大会オファー')
    
@section('content')
    <form action="#" method="POST">
    @csrf

    @foreach($competition->roles as $role)
        <h5>{{ $role->name }}</h5>
        @php
            $start = \Carbon\Carbon::parse($competition->start_day);
            $end = \Carbon\Carbon::parse($competition->end_day);
            $dates = [];
            for ($date = $start; $date <= $end; $date->addDay()) {
                $dates[] = $date->copy();
            }
        @endphp

        @foreach($dates as $date)
            <label>
                <input type="checkbox" name="attendance[{{ $role->id }}][]" value="{{ $date->format('Y-m-d') }}">
                {{ $date->format('m/d') }}
            </label>
        @endforeach
    @endforeach

    <button type="submit">送信</button>
</form>

@endsection