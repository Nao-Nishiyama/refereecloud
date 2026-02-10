@extends('layouts.app')

@section('title', '大会一覧')

@section('content')

@php
    $user = Auth::user();
@endphp

<div class="container">
    <table class="table align-middle table-hover bg-white border text-secondary text-center shadow w-7">
      <thead class="small table-success text-secondary">
        <tr>
          <th>大会名</th>
          <th>会場</th>
          <th class="text-nowrap">募集日程</th>
          <th class="text-nowrap">締切</th>
          <th class="text-center">申込</th>
        </tr>
      </thead>

      <tbody class="text-start">
        @if ($all_competitions->isNotEmpty())
          @foreach ($all_competitions as $competition)
            @php
              $start = \Carbon\Carbon::parse($competition->start_day);
              $end   = \Carbon\Carbon::parse($competition->end_day);

              $deadline = $competition->application_deadline
                  ? \Carbon\Carbon::parse($competition->application_deadline)->endOfDay()
                  : null;

              $isClosed = $deadline ? $deadline->isPast() : false;

              // 対象者かどうか（コントローラで作った eligibleMap）
              $isEligible = !empty($eligibleMap[$competition->id]);

              // 申込済み判定（※本当はコントローラでまとめて渡すのがベター）
              $applied = false;
              if ($isEligible && !$isClosed) {
                  $applied = $user->applications()
                      ->whereHas('nomination', fn($q) => $q->where('competition_id', $competition->id))
                      ->exists();
              }
            @endphp

            <tr>
              {{-- 大会名（長い場合は省略＋title） --}}
              <td class="d-ellipsis">
                {{-- <span class="truncate" title="{{ $competition->name }}"> --}}
                  {{ $competition->name }}
                {{-- </span> --}}
              </td>

              <td>
                <div class="fw-semibold">
                  <span class="truncate" title="{{ $competition->venue }}">
                    {{ $competition->venue }}
                  </span>
                </div>
                <div class="small text-muted">{{ $competition->city }}</div>
              </td>

              {{-- 募集日程：同日なら1つ、違うなら範囲（年は開始日のみ） --}}
              <td class="text-nowrap">
                {{ $start->format('y/n/j') }}
                @if (!$start->isSameDay($end))
                  –
                  {{ $end->format('n/j') }}
                @endif
              </td>

              {{-- 締切 --}}
              <td class="text-nowrap">
                {{ $deadline ? $deadline->format('n/j') : '-' }}
              </td>

              {{-- 申込 --}}
              <td class="text-center text-nowrap">
                @if ($isClosed)
                  <a href="{{ route('competition.apply', $competition->id) }}" class="text-muted text-decoration-none">
                    詳細
                  </a>
                @elseif ($isEligible)
                  @if ($applied)
                    <a href="{{ route('competition.apply', $competition->id) }}" class="text-secondary text-decoration-none">
                      申込済
                    </a>
                  @else
                    <a href="{{ route('competition.apply', $competition->id) }}" class="text-danger text-decoration-none">
                      申込
                    </a>
                  @endif
                @else
                  <span class="text-muted">対象外</span>
                @endif
              </td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="6" class="text-center">募集なし</td></tr>
        @endif
      </tbody>
    </table>
</div>

@endsection