@extends('layouts.app')

@section('title', 'Admin_Competition_Control')

@section('content')
<div class="row gx-5 d-flex justify-content-center">
  <div class="col-md-8">
    <h2 class="text-dark">
      {{ $competition->name }} - {{ $competition->type->name }}
    </h2>
    <p class="text-dark ms-5">
      開催地：{{ $competition->city }}<br>
      会場：{{ $competition->venue }}<br>
      募集日程：{{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
      〜 {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}<br>
      締切：{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}
    </p>
    <hr>

    @if (session('status'))
      <div class="alert alert-info">{{ session('status') }}</div>
    @endif
      @php
        // 日付でグループ化
        $grouped = $nominations->groupBy(fn($n) => optional($n->day?->date)->toDateString());
      @endphp

      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:20%">日付</th>
            <th style="width:20%">役職</th>
            <th style="width:60%">参加希望者</th>
          </tr>
        </thead>
        <tbody>
        @forelse($grouped as $dateKey => $rows)
          @php
            $rowspan   = $rows->count();
            $dateLabel = $dateKey ? \Carbon\Carbon::parse($dateKey)->format('Y/m/d') : '-';
          @endphp

          @foreach ($rows as $idx => $n)
            <tr>
                @if ($idx === 0)
                    <td rowspan="{{ $rowspan }}" class="align-middle fw-semibold">
                    {{ $dateLabel }}
                    </td>
                @endif

                <td>
                    {{ $n->official?->name ?? '-' }}
                </td>

                <td>
                @forelse ($n->applications as $ap)
                    {{ $ap->user->referee->surname_kanji }} {{ $ap->user->referee->name_kanji }}
                    @if (!$loop->last) 、@endif
                @empty
                    <span class="text-muted small">申込なし</span>
                @endforelse
                </td>
            </tr>
          @endforeach
        @empty
          <tr><td colspan="3" class="text-muted">募集セル（nomination）がありません。</td></tr>
        @endforelse
        </tbody>
      </table>
  </div>
</div>
@endsection