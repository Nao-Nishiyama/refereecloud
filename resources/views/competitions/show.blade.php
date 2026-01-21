@extends('layouts.app')

@section('title', '大会一覧')

@section('content')

@php
    $user = Auth::user();
@endphp

<div class="row gx-5 d-flex justify-content-center table-responsive">
  <table class="table align-middle table-hover bg-white border text-secondary w-75 text-center shadow">
    <thead class="small table-success text-secondary">
      <tr>
          <th>大会名</th>
          <th>種別</th>
          <th>開催地</th>
          <th>会場</th>
          <th>募集日程</th>
          <th>締切</th>
          <th>申込</th>
      </tr>
    </thead>
    <tbody>
      @if ($all_competitions->isNotEmpty())
        @foreach ($all_competitions as $competition)
          <tr>
            <td>{{ $competition->name }}</td>
            <td>{{ $competition->type->name }}</td>
            <td>{{ $competition->city }}</td>
            <td>{{ \Illuminate\Support\Str::limit($competition->venue, 10) }}</td>
            <td>
              {{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
              〜
              {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}
            </td>
            <td>{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}</td>
            <td>
            @php
                // 受付締切（null かもしれない想定）
                $deadline = $competition->application_deadline
                ? \Carbon\Carbon::parse($competition->application_deadline)
                : null;

                // 当日いっぱい受け付けたい場合は endOfDay() を付ける
                $isClosed = $deadline ? $deadline->endOfDay()->isPast() : false;

                // 対象者かどうか（コントローラで作った eligibleMap を使用）
                $isEligible = !empty($eligibleMap[$competition->id]);
            @endphp

            @if ($isClosed)
                <a href="{{ route('competition.apply', $competition->id) }}" class="text-muted text-decoration-none">詳細</a>
            @elseif ($isEligible)
                @php
                $applied = $user->applications()
                    ->whereHas('nomination', fn($q) => $q->where('competition_id', $competition->id))
                    ->exists();
                @endphp

                @if ($applied)
                  申込済
                @endif
                <a href="{{ route('competition.apply', $competition->id) }}" class="text-danger text-decoration-none">申込</a>
            @else
                <span class="text-muted">対象外</span>
            @endif
            </td>
          </tr>
        @endforeach
      @else
        <tr><td colspan="7">募集なし</td></tr>
      @endif
    </tbody>
  </table>
</div>
@endsection