@extends('layouts.app')

@section('title', '審判員登録情報管理')
    

@section('content')
    @php
        $year = now()->year;

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

  <div class="container">
    <div class="row g-2 align-items-end mb-3">

      {{-- ▼ フィルタ用フォーム（GET） --}}
      <form id="filters" method="get" action="{{ route('admin.referees.approval') }}"
            class="col-9 row g-2 align-items-end">

        <div class="col-3">
          <label class="form-label mb-1" style="font-size: 0.9em;">資格</label>
          <select name="license" class="form-select" onchange="this.form.submit()">
            <option value="">すべて（{{ $countsByLic->sum() }}）</option>
            @foreach($lics as $lic)
              <option value="{{ $lic->id }}" @selected((string)$licId === (string)$lic->id)>
                {{ $lic->name }}（{{ $countsByLic[$lic->id] ?? 0 }}）
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-3">
          <label class="form-label mb-1" style="font-size: 0.9em;">団体</label>
          <select name="organization" class="form-select" onchange="this.form.submit()">
            <option value="">すべて（{{ $countsByOrg->sum() }}）</option>
            @foreach($orgs as $org)
              <option value="{{ $org->id }}" @selected((string)$orgId === (string)$org->id)>
                {{ $org->short_name }}（{{ $countsByOrg[$org->id] ?? 0 }}）
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-2">
          <label class="form-label mb-1" style="font-size: 0.9em;">承認年度</label>
          <select name="approved_year" class="form-select" onchange="this.form.submit()">
            <option value="">指定なし</option>
            @foreach($years as $y)
              <option value="{{ $y }}" @selected((string)$approvedYear === (string)$y)>{{ $y }}年度</option>
            @endforeach
          </select>
        </div>

        <div class="col-4 d-flex align-items-center gap-2">
          <a href="{{ route('admin.referees.approval') }}" class="btn btn-sm btn-outline-dark"  style="font-size: 0.9em;">リセット</a>

          @php
            $appBase   = route('admin.referees.approval');
            $appCommon = [
              'license'       => $licId,
              'organization'  => $orgId,
              'trashed'       => $mode ?: null,
              'approved_year' => $approvedYear ?: null,
            ];
            $urlApplicants      = $appBase.'?'.http_build_query($appCommon + ['applicants' => 1]);
            $urlClearApplicants = $appBase.'?'.http_build_query($appCommon);
          @endphp

          <div class="btn-group">
            <a href="{{ $urlApplicants }}"
              class="btn btn-sm {{ $onlyApplicants ? 'btn-primary' : 'btn-outline-primary' }}">
              新規申請者
            </a>
            @if($onlyApplicants)
              <a href="{{ $urlClearApplicants }}" class="btn btn-sm btn-outline-secondary">解除</a>
            @endif
          </div>
        </div>
      </form>

      {{-- ▼ 右カラム：全員承認（POST） ※別フォーム！ --}}
      <div class="col-3 d-flex justify-content-end">
        <form method="post"
              action="{{ route('admin.referee.approvals.bulkApprove') }}"
              onsubmit="return confirm('表示されている未承認者をすべて承認します。よろしいですか？')">
          @csrf
          {{-- 現在のフィルタ条件を引き継ぐ --}}
          <input type="hidden" name="license"        value="{{ $licId }}">
          <input type="hidden" name="organization"   value="{{ $orgId }}">
          <input type="hidden" name="trashed"        value="{{ $mode }}">
          <input type="hidden" name="applicants"     value="{{ $onlyApplicants ? 1 : 0 }}">
          <input type="hidden" name="approved_year"  value="{{ $approvedYear }}">

          <button class="btn btn-sm btn-success">全員承認</button>
        </form>
      </div>
    </div>

      <script>
        document.getElementById('filters')?.addEventListener('submit', function () {
          Array.from(this.elements).forEach(el => {
            if (el.name && (el.value ?? '').trim() === '') el.disabled = true;
          });
          setTimeout(() => Array.from(this.elements).forEach(el => el.disabled = false), 0);
        });
      </script>

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
      <table class="table table-sm align-middle text-center">
        <thead>
          <tr>
              <th>資格</th>
              <th>氏名</th>
              <th>データ</th>
              <th>詳細</th>
              <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($refs as $r)
            @php
              $recentYears = [$year, $year-1, $year-2, $year-3];

              $userReports   = $r->user?->reports ?? collect();
              $noReports3Yrs = $userReports->whereIn('year', [$year-1, $year-2, $year-3])->isEmpty();

              $isTrashed   = method_exists($r,'trashed') && $r->trashed();

              $ap                 = $r->approval;
              $approvedYearValue  = (int) ($ap->year ?? 0);
              $approved           = (bool) ($ap->approved ?? false);
              $hasRecentApproval  = $approved && in_array($approvedYearValue, $recentYears, true);

              $isApplicant = $ap && $ap->year == $fiscalYear && $ap->suspended == 1 && $ap->approved == 0;

              $isApprovedSelectedYear = $approvedYear && $ap && $ap->year == $approvedYear && $ap->approved == 1;
            @endphp

          <tr @if($isTrashed) class="text-muted" @endif>
              <td>{{ $r->license->name }}</td>
              <td>{{ $r->surname_kanji }} {{ $r->name_kanji }}</td>
              <td>
                <a href="{{ route('admin.referees.edit', $r->id)}}">
                  <i class="fa-regular fa-address-card"></i>
                </a>
              </td>

              {{-- ▼ 詳細（今年の報告があればリンク、なければ表示） --}}
            <td>
              @if ($noReports3Yrs)
                @if ($approved)
                  <span class="text-muted" style="font-size:.8em">承認（{{ $approvedYearValue }}年度）</span>
                @else
                  <span class="text-muted" style="font-size:.8em">３年間未報告</span>
                @endif
              @else
                @if ($r->user)
                  <a href="{{ route('reports.show', $r->user->id) }}"><i class="fa-solid fa-clipboard text-success"></i></a>
                @else
                  <span class="text-muted">-</span>
                @endif
              @endif
            </td>

            {{-- ▼ 操作（どちらか一方だけ表示） --}}
            @php
                $recentYears = $recentYears ?? [ $year, $year-1, $year-2, $year-3 ];

                $isTrashed   = method_exists($r,'trashed') && $r->trashed();
                $isSuspended = (bool)($ap?->suspended); 
                $authorized  = optional(Auth::user()->referee)->organization_id === $r->organization_id;
                $isAdmin     = Auth::user()?->role_id === \App\Models\User::ADMIN_ROLE_ID;

                // 1対1リレーション前提：null安全に年を取得
                $approvedYear = $r->approval?->year;              // int|null
                $hasRecentApproval = $approvedYear
                                    && in_array((int)$approvedYear, $recentYears, true);
            @endphp

            <td class="text-nowrap">
              @if ($isTrashed)
                {{-- 抹消済み：復元のみ（admin） --}}
                @can('referees.restore')
                  <form action="{{ route('admin.referees.restore', $r->id) }}" method="post" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-success">復元</button>
                  </form>
                @else
                  <span class="text-muted">抹消中</span>
                @endcan
              @elseif ($isSuspended)
                {{-- ★ suspended=true の人は常に「承認」「抹消」を表示 --}}
                @can('referees.approve')
                  <form action="{{ route('admin.referee.approvals.approve', ['referee'=>$r->id,'year'=>$year]) }}"
                        method="post" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-sm btn-outline-primary">承認</button>
                  </form>
                @endcan
                @can('referees.delete')
                  <form action="{{ route('admin.referee.approvals.destroy', ['referee'=>$r->id,'year'=>$year]) }}"
                        method="post" class="d-inline ms-1"
                        onsubmit="return confirm('本当に抹消しますか？');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">抹消</button>
                  </form>
                @endcan

              @else
                @if (!$hasRecentApproval)
                  @can('referees.approve')
                    <form action="{{ route('admin.referee.approvals.approve', ['referee'=>$r->id,'year'=>$year]) }}"
                          method="post" class="d-inline">
                      @csrf @method('PATCH')
                      <button class="btn btn-sm btn-outline-primary">承認</button>
                    </form>
                  @endcan
                  @can('referees.delete')
                    <form action="{{ route('admin.referee.approvals.destroy', ['referee'=>$r->id,'year'=>$year]) }}"
                          method="post" class="d-inline ms-1"
                          onsubmit="return confirm('本当に抹消しますか？');">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger">抹消</button>
                    </form>
                  @endcan
                @else
                  <span class="text-muted">-</span>
                @endif
              @endif
            </td>
{{-- 
            <td>
              @if ($isApplicant)
                <span class="badge text-bg-warning">申請中（{{ $ap->year }}年度）</span>
              @elseif ($r->approval)
                <span class="badge text-bg-success">承認済（{{ $ap->year }}年度）</span>
              @else
                <span class="text-muted">-</span>
              @endif
            </td> --}}

          </tr>
          @endforeach
        </tbody>
      </table>
  </div>
@endsection
