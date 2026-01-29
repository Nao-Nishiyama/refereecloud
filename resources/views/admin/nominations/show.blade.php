@extends('layouts.app')

@section('title','全大会：団体別割当一覧')

@section('content')

<style>
/* ---- スクロール容器 ---- */
.table-wrap{
  max-width: 100%;
  max-height: 70vh;
  overflow: auto;                 /* 縦横どちらも */
  -webkit-overflow-scrolling: touch;
}

/* ---- テーブル本体 ---- */
.table.freeze-three{
  width: max-content;             /* 親幅を超えさせて横スクロール発生 */
  border-collapse: separate; 
  border-spacing: 0;
  table-layout: fixed;
  white-space: nowrap;            /* 折り返し防止（必要に応じて外してOK） */
}

/* 左3列の固定幅（※変更したら .sticky-2/.sticky-3 の left も合わせる） */
.w-col-1 { width: 120px; min-width: 120px; }  /* 大会名 */
.w-col-2 { width: 120px; min-width: 120px; }  /* 実施日 */
.w-col-3 { width: 160px; min-width: 160px; }  /* 役職 */

/* ヘッダ固定（最上段） */
.table.freeze-three thead th{
  position: sticky;
  top: 0;
  z-index: 5;
  background: #f8f9fa;           /* Safari 対策 */
  box-shadow: 0 2px 0 rgba(0,0,0,.05);
}

/* 左3列固定（left は累積幅に！） */
.table.freeze-three .sticky-1{
  position: sticky; left: 0;
  z-index: 6; background: #fff;
  box-shadow: 2px 0 0 rgba(0,0,0,.05);
}
.table.freeze-three .sticky-2{
  position: sticky; left: 120px;             /* = .w-col-1 */
  z-index: 6; background: #fff;
  box-shadow: 2px 0 0 rgba(0,0,0,.05);
}
.table.freeze-three .sticky-3{
  position: sticky;             /* 1列目 */
  left: calc(120px + 120px);                 /* 1+2列目 */
  z-index: 6; background: #fff;
  box-shadow: 2px 0 0 rgba(0,0,0,.05);
}

/* 左上ヘッダの z-index を少し前面へ */
.table.freeze-three thead .sticky-1,
.table.freeze-three thead .sticky-2,
.table.freeze-three thead .sticky-3 { z-index: 7; }

/* 右側の団体列の最小幅（見やすさ用） */
.org-col { min-width: 80px; }

/* 合計セルの幅 */
.total-col { min-width: 100px; }

/* 入力の横幅（小型） */
.cap-input { width: 64px; }
</style>

<div class="container">
  <div class="row">
    <div class="col-6">
      <form method="GET" action="{{ route('admin.nominations.capacities.show') }}" class="mb-3 d-flex align-items-center gap-2">
        <label class="mb-0">表示年度</label>
        <select name="year" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
          @for($y = $year - 3; $y <= $year + 3; $y++)
          <option value="{{ $y }}" @selected($y == $year)>{{ $y }}年度</option>
          @endfor
        </select>
        {{-- 期間の注記（任意） --}}
        <small class="text-muted">
          表示期間: {{ $from->format('Y/m/d') }} 〜 {{ $to->format('Y/m/d') }}
        </small>
      </form>
    </div>
    <div class="col-6 text-end">
      <a href="{{ route('admin.nominations.capacities.edit', ['year' => $year]) }}" class="btn btn-outline-dark">編集</a>
    </div>
  </div>

  <h5 class="mb-3 py-3">{{$y-4}}年度 大会の団体別割当一覧</h5>

  @if(session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
  @endif

    @php
      // 「審判員が1人もいない団体」は除外（withCount('referees') 済み前提）
      $visibleOrgs = isset($organizations)
        ? $organizations->filter(fn($o) => (int)($o->referees_count ?? 0) > 0)->values()
        : collect(); // 念のためのフォールバック

      // 既存値の配列（無ければ空配列）
      $caps = $caps ?? [];
    @endphp

    <div class="table-wrap">
      <table class="table table-sm table-striped align-middle freeze-three">
        <thead>
          <tr>
            <th class="sticky-1 w-col-1">大会名</th>
            <th class="sticky-2 w-col-2">実施日</th>
            <th class="sticky-3 w-col-3">役職</th>
            @foreach ($visibleOrgs as $org)
              <th class="text-center org-col">
                {{ $org->short_name }}
              </th>
            @endforeach
            <th class="text-center total-col">合計</th>
          </tr>
        </thead>
        <tbody>
        @foreach ($competitions as $competition)
          @php
            // 大会内の nomination を「日付 → official_id」で並べ、日付ごとにグループ化
            $grouped = $competition->nominations
              ->sortBy([['day.date','asc'], ['official_id','asc']])
              ->groupBy(fn($n) => optional($n->day?->date)->toDateString());

            // 大会セルの rowSpan（この大会の全行数）
            $rowsCountForCompetition = $competition->nominations->count();
            $printedCompetitionCell = false;
          @endphp

          @forelse ($grouped as $dateKey => $rowsSameDay)
            @php
              // 実施日表示
              $dateLabel = $dateKey ? \Carbon\Carbon::parse($dateKey)->format('m月d日') : '-';
              // 同日の行数（役職の個数）
              $rowspanDate = $rowsSameDay->count();
            @endphp

            @foreach ($rowsSameDay as $idx => $nom)
              @php
                // 行合計の初期値（既存値を合計）
                $rowTotal = 0;
                foreach ($visibleOrgs as $org) {
                  $rowTotal += (int)($caps[$nom->id][$org->id] ?? 0);
                }
              @endphp

              <tr>
                {{-- 大会名セル：大会の最初の行だけ表示して rowSpan で結合 --}}
                @if (!$printedCompetitionCell)
                  <th class="sticky-1 w-col-1 align-middle" rowspan="{{ $rowsCountForCompetition }}">
                    {{ $competition->name }}
                  </th>
                  @php $printedCompetitionCell = true; @endphp
                @endif

                {{-- 実施日セル：同一日の最初の行だけ表示して rowSpan で結合 --}}
                @if ($idx === 0)
                  <td class="sticky-2 w-col-2 align-middle" rowspan="{{ $rowspanDate }}">
                    {{ $dateLabel }}
                  </td>
                @endif

                {{-- 役職（常に1行に1つ） --}}
                <td class="sticky-3 w-col-3 align-middle">
                  {{ $nom->official?->name ?? '-' }}
                </td>

                {{-- 団体ごとの人数入力 --}}
                @foreach ($visibleOrgs as $org)
                  @php
                    $raw = $caps[$nom->id][$org->id] ?? null;
                    $val = old("capacity.{$nom->id}.{$org->id}", $raw);
                  @endphp
                  <td class="text-center">{{ $val }}</td>
                @endforeach

                <td class="text-center">
                  <span class="row-total" data-row="row-{{ $nom->id }}">{{ $rowTotal }}</span>
                </td>
              </tr>
            @endforeach
          @empty
            {{-- nomination が無い大会 --}}
            <tr>
              <th class="sticky-1 w-col-1 align-middle">{{ $competition->name }}</th>
              <td class="sticky-2 w-col-2" colspan="{{ 1 + $visibleOrgs->count() + 1 }}">
                この大会には募集セル（nomination）がありません。
              </td>
            </tr>
          @endforelse
        @endforeach
        </tbody>
      </table>
    </div>

    {{-- 行合計を入力に連動して更新 --}}
    <script>
      document.addEventListener('input', function(e){
        if (!e.target.classList.contains('cap-input')) return;
        const rowKey = e.target.dataset.row;
        let sum = 0;
        document.querySelectorAll('.cap-input[data-row="'+rowKey+'"]').forEach(inp => {
          const v = parseInt(inp.value, 10);
          if (!isNaN(v)) sum += v;
        });
        const totalEl = document.querySelector('.row-total[data-row="'+rowKey+'"]');
        if (totalEl) totalEl.textContent = String(sum);
      });
    </script>
</div>
@endsection