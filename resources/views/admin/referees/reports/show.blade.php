@extends('layouts.app')

@section('title', '活動報告一覧')
    

@section('content')
    @php
    $year = now()->subYear()->year;

    // trashed モードの安全な初期化（'', 'with', 'only' 以外は '' に落とす）
    $mode = $mode
        ?? request()->string('trashed')->toString()
        ?? '';

    $mode = in_array($mode, ['', 'with', 'only'], true) ? $mode : '';

    $base = request()->url();
    $common = [
      'license' => $licId,
      'organization' => $orgId,
    // ページ番号は切り替え時にリセット
    ];

    // 抹消表示権限（Gateで判定。@can 使うなら下行は不要）
    $canViewTrashed = $canViewTrashed
        ?? \Illuminate\Support\Facades\Gate::allows('referees.viewTrashed');
@endphp

<div class="container w-75">
    <form method="get" action="{{ route('admin.referees.reports.show') }}" class="row g-2 align-items-end mb-3" id="filters">
      <div class="col-3">
        <label class="form-label mb-1">資格</label>
        <select name="license" class="form-select" onchange="this.form.submit()">
          <option value="">すべて（{{ $countsByLic->sum() }}）</option>
          @foreach($lics as $lic)
            <option value="{{ $lic->id }}"
              @selected((string)$licId === (string)$lic->id)>
              {{ $lic->name }}（{{ $countsByLic[$lic->id] ?? 0 }}）
            </option>
          @endforeach
        </select>
      </div>
    
      <div class="col-3">
        <label class="form-label mb-1">団体</label>
        <select name="organization" class="form-select" onchange="this.form.submit()">
          <option value="">すべて（{{ $countsByOrg->sum() }}）</option>
          @foreach($orgs as $org)
            <option value="{{ $org->id }}"
              @selected((string)$orgId === (string)$org->id)>
              {{ $org->short_name }}（{{ $countsByOrg[$org->id] ?? 0 }}）
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-3">
        <button class="btn btn-outline-secondary" hidden>絞り込み</button>
        <a href="{{ route('admin.referees.reports.show') }}" class="btn btn-outline-dark">リセット</a>
      </div>
    </form>

    <div>
      <div class="btn-group mb-3" role="group">
          {{-- 通常 --}}
          <a class="btn btn-sm {{ $mode==='' ? 'btn-secondary' : 'btn-outline-secondary' }}"
          href="{{ $base.'?'.http_build_query($common) }}">
          通常
          </a>

          @can('referees.viewTrashed')
          {{-- 抹消含む --}}
          <a class="btn btn-sm {{ $mode==='with' ? 'btn-secondary' : 'btn-outline-secondary' }}"
              href="{{ $base.'?'.http_build_query($common + ['trashed'=>'with']) }}">
              抹消含む
          </a>

          {{-- 抹消のみ --}}
          <a class="btn btn-sm {{ $mode==='only' ? 'btn-secondary' : 'btn-outline-secondary' }}"
              href="{{ $base.'?'.http_build_query($common + ['trashed'=>'only']) }}">
              抹消のみ
          </a>
          @endcan
      </div>
  </div>

    {{$year}}年度 活動報告一覧
    <hr>

    <table class="table table-sm align-middle text-center">
      <thead>
        <tr>
            <th>資格</th>
            <th>氏名</th>
            <th>主</th>
            <th>副</th>
            <th>記</th>
            <th>AS</th>
            <th>線</th>
            <th>詳細</th>
            <th></th>
        </tr>
        <tr>
        </tr>
      </thead>
      <tbody>
        @forelse ($refs as $r)
            @php
                // 当年はコントローラから $year 渡し済み前提
                $years3 = [$year, $year - 1, $year - 2];

                // userが無い時でも落ちないように
                $userReports = $r->user?->reports ?? collect();

                // 当年の行用（既存）
                $reportsThisYear = $userReports->where('year', $year);

                // 3年分に1件でもあれば false、1件も無ければ true
                $noReports3Years = $userReports->whereIn('year', $years3)->isEmpty();
            @endphp
          <tr @if(method_exists($r,'trashed') && $r->trashed()) class="text-muted" @endif>
            <td>{{ $r->license->name }}</td>
            <td>{{ $r->surname_kanji }} {{ $r->name_kanji }}</td>
            @php
              $reports = ($r->user?->reports ?? collect())->where('year', $year);
            @endphp
            @forelse ($reports as $rp)
                <td>{{ $rp->first_ref_block + $rp->first_ref }}</td>
                <td>{{ $rp->second_ref_block + $rp->second_ref }}</td>
                <td>{{ $rp->scorer }}</td>
                <td>{{ $rp->assistant_scorer }}</td>
                <td>{{ $rp->linejudge }}</td>
                <td><a href="{{ route('reports.show', $r->user->id)}}"><i class="fa-solid fa-clipboard text-success"></i></a></td>
                <td></td>
            @empty
                <td><span class="text-muted">-</span></td>
                <td><span class="text-muted">-</span></td>
                <td><span class="text-muted">-</span></td>
                <td><span class="text-muted">-</span></td>
                <td><span class="text-muted">-</span></td>
                <td>
                  @if ($noReports3Years)
                    <span class="text-muted" style="font-size: 0.75em">３年間未報告</span>
                  @else
                    <a href="{{ route('reports.show', $r->user->id)}}"><i class="fa-solid fa-clipboard text-success"></i></a>
                  @endif
                </td>

                @php
                    $isTrashed = method_exists($r,'trashed') && $r->trashed();
                    $authorized = $r->organization_id == Auth::user()->referee->organization_id;
                    $admin = Auth::user()->role_id == 1;
                    $approved   = (bool) ($r->approval?->approved ?? false);
                @endphp

                <td class="text-nowrap">
                  @if ($authorized || $admin)
                    @if ($isTrashed)
                      {{-- 抹消済み → 復元のみ（adminだけ） --}}
                      @can('referees.restore')
                        <span class="text-muted" style="font-size: 0.75em">抹消中</span>
                        <form action="{{ route('admin.referees.restore', $r->id) }}" method="post" class="d-inline">
                          @csrf @method('PATCH')
                          <button class="btn btn-sm btn-outline-success">復元</button>
                        </form>
                      @endcan

                    @elseif (!$approved)
                      {{-- ★ 未承認のときだけ抹消ボタンを表示 --}}
                      @can('referees.delete')
                        <form action="{{ route('admin.referee.approvals.destroy', ['referee' => $r->id, 'year' => $year]) }}"
                              method="post" class="d-inline"
                              onsubmit="return confirm('本当に抹消しますか？');">
                          @csrf @method('DELETE')
                          <button class="btn btn-sm btn-outline-danger">抹消</button>
                        </form>
                      @endcan

                    @else
                      {{-- 承認済みは何も出さない --}}
                      <span class="text-muted">-</span>
                    @endif
                  @endif
                </td>
            @endforelse
          </tr>
        @empty
          <tr><td colspan="9" class="text-muted">該当なし</td></tr>
        @endforelse
      </tbody>
    </table>
</div>
@endsection
