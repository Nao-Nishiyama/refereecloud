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

    <form method="POST" action="{{ route('admin.competitions.assign', $competition->id) }}">
      @csrf
      @method('PATCH')

      @php
        // 日付でグループ化
        $grouped = $nominations->groupBy(fn($n) => optional($n->day?->date)->toDateString());
        $myOrgId = optional(Auth::user()->referee)->organization_id;
      @endphp

      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:20%">日付</th>
            <th style="width:30%">役職</th>
            <th style="width:50%">派遣審判員</th>
          </tr>
        </thead>
        <tbody>
        @forelse($grouped as $dateKey => $rows)
          @php
            $rowspan   = $rows->count();
            $dateLabel = $dateKey ? \Carbon\Carbon::parse($dateKey)->format('Y/m/d') : '-';
          @endphp

          @foreach ($rows as $idx => $n)
            @php
              // 自団体に割り当てられた枠数（無ければ 0）
              $slots = (int) ($capByNomination[$n->id] ?? 0);

              // 事前選択を枠数に合わせて切り詰め & 足りなければ null で埋める
              $pre = array_slice($preAssigned[$n->id] ?? [], 0, $slots);
              $pre = array_pad($pre, $slots, null);

              // 候補者（コントローラ側で自団体＆条件フィルタ済みの想定）
              $cands = $candidatesByNomination[$n->id] ?? collect();
            @endphp

            <tr>
              @if ($idx === 0)
                <td rowspan="{{ $rowspan }}" class="align-middle fw-semibold">
                  {{ $dateLabel }}
                </td>
              @endif

              <td>
                {{ $n->official?->name ?? '-' }}
                @if($slots > 0)
                  <span class="badge text-bg-primary ms-2">割当：{{ $slots }}</span>
                @else
                  <span class="badge text-bg-secondary ms-2">割当：0</span>
                @endif
              </td>

              <td>
                @if ($slots > 0)
                  <div class="d-flex gap-2 flex-wrap">
                    @for ($i = 0; $i < $slots; $i++)
                      <select name="assignments[{{ $n->id }}][]" class="form-select" style="min-width:220px;">
                        <option value="" selected>* *</option>
                        @foreach ($cands as $r)
                          <option value="{{ $r->id }}" @selected($pre[$i] === $r->id)>
                            {{ $r->organization->short_name }}｜{{ $r->surname_kanji }} {{ $r->name_kanji }}
                          </option>
                        @endforeach
                      </select>
                    @endfor
                  </div>
                  @if($cands->isEmpty())
                    <div class="small text-muted mt-1">自団体かつ条件に合致する候補者がいません。</div>
                  @endif
                @else
                  <span class="text-muted small">（この役職・日付に自団体の割当はありません）</span>
                @endif
              </td>
            </tr>
          @endforeach
        @empty
          <tr><td colspan="3" class="text-muted">募集セル（nomination）がありません。</td></tr>
        @endforelse
        </tbody>
      </table>
      
      <div class="text-center">
        <button type="submit" class="btn btn-primary px-4">保存</button>
        <a href="{{route('admin.competitions.show')}}" class="btn btn-dark px-4">戻る</a>
      </div>

    </form>
  </div>
</div>
@endsection