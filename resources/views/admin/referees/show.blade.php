@extends('layouts.app')

@section('title', '審判員一覧')

@section('content')
@php
  // ---- 権限/状態 ----
  $user = Auth::user();
  $isAdmin     = ($user->role_id === 1);
  $isCommittee = ($user->role_id === 2);
  $isChief     = ($user->role_id === 3);

  // trashed モード（'', 'with', 'only' 以外は '' に落とす）
  $mode = $mode ?? request()->string('trashed')->toString() ?? '';
  $mode = in_array($mode, ['', 'with', 'only'], true) ? $mode : '';

  $baseUrl = request()->url();

  // btn-group のリンクに引き継ぐクエリ（ページは落とす）
  $common = array_filter([
    'license'      => $licId ?: null,
    'organization' => $orgId ?: null,
  ], fn($v) => !is_null($v) && $v !== '');

  // 抹消表示権限（controllerで渡しているならそれを優先、無ければGate）
  $canViewTrashed = $canViewTrashed
      ?? \Illuminate\Support\Facades\Gate::allows('referees.viewTrashed');

  // フィルタ表示用ラベル
  $filterParts = [];
  if (!empty($licId)) {
    $filterParts[] = '資格：' . (optional($lics->firstWhere('id', $licId))->name ?? '-');
  }
  if (!empty($orgId)) {
    $orgObj = $orgs->firstWhere('id', $orgId);
    $filterParts[] = '団体：' . ($orgObj->short_name ?? $orgObj->full_name ?? '-');
  }
@endphp

@if ($allRefs->isNotEmpty())

  {{-- ▼ ① 通常/抹消 切替（admin/committee かつ 権限ありの場合だけ） --}}
  @if (($isAdmin || $isCommittee) && $canViewTrashed)
    <div class="row gx-5 d-flex justify-content-center table-responsive">
      <div class="col-12 col-lg-10">
        <div class="btn-group mb-3" role="group" aria-label="trashed mode">
          <a class="btn btn-sm {{ $mode==='' ? 'btn-secondary' : 'btn-outline-secondary' }}"
             href="{{ $baseUrl.'?'.http_build_query($common) }}">
            通常
          </a>
          <a class="btn btn-sm {{ $mode==='with' ? 'btn-secondary' : 'btn-outline-secondary' }}"
             href="{{ $baseUrl.'?'.http_build_query($common + ['trashed'=>'with']) }}">
            抹消含む
          </a>
          <a class="btn btn-sm {{ $mode==='only' ? 'btn-secondary' : 'btn-outline-secondary' }}"
             href="{{ $baseUrl.'?'.http_build_query($common + ['trashed'=>'only']) }}">
            抹消のみ
          </a>
        </div>
      </div>
    </div>
  @endif

  {{-- ▼ ② フィルタ（資格/団体） --}}
  <div class="row gx-5 d-flex justify-content-center table-responsive">
    <div class="col-12 col-lg-10">

      <form method="GET" action="{{ route('admin.referees.show') }}" class="mb-2">
        <div class="row g-2 align-items-end">

          {{-- 資格 --}}
          <div class="col-12 col-md-4">
            <label class="form-label mb-1" style="font-size: 0.9em;">資格</label>
            <select name="license" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="">すべて（{{ $countsByLic->sum() }}）</option>
              @foreach($lics as $lic)
                <option value="{{ $lic->id }}" @selected((string)$licId === (string)$lic->id)>
                  {{ $lic->name }}（{{ $countsByLic[$lic->id] ?? 0 }}）
                </option>
              @endforeach
            </select>
          </div>

          {{-- 団体（admin/committee は選択可、chief は固定） --}}
          @if ($isAdmin || $isCommittee)
            <div class="col-12 col-md-4">
              <label class="form-label mb-1" style="font-size: 0.9em;">団体</label>
              <select name="organization" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">すべて（{{ $countsByOrg->sum() }}）</option>
                @foreach($orgs as $org)
                  <option value="{{ $org->id }}" @selected((string)$orgId === (string)$org->id)>
                    {{ $org->short_name }}（{{ $countsByOrg[$org->id] ?? 0 }}）
                  </option>
                @endforeach
              </select>
            </div>
          @else
            <input type="hidden" name="organization" value="{{ optional($user->referee)->organization_id }}">
          @endif

          {{-- trashed モードは btn-group で切替えるので hidden で保持（切替を維持） --}}
          <input type="hidden" name="trashed" value="{{ $mode }}">

          {{-- フィルタ変更時は 1ページ目に戻す（ページングがある場合用） --}}
          <input type="hidden" name="page" value="1">

          <div class="col-12 col-md-auto">
            <a href="{{ route('admin.referees.show') }}" class="btn btn-sm btn-outline-secondary">
              解除
            </a>
          </div>

        </div>
      </form>

      {{-- フィルタ表示 --}}
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="small text-muted">
          フィルタ：{{ $filterParts ? implode(' / ', $filterParts) : 'なし' }}
        </div>
      </div>

      {{-- ▼ ③ 一覧テーブル --}}
      <table class="table align-middle table-hover bg-white border text-secondary text-center shadow">
        <thead class="small table-success text-secondary">
          <tr>
            <th>氏名</th>
            <th>登録番号</th>
            <th>所属</th>
            <th>資格</th>
            <th>紐付け</th>
            <th>詳細</th>
          </tr>
        </thead>
        <tbody class="text-start">
          @if ($refs->count() === 0)
            <tr>
              <td colspan="6" class="text-muted text-center">該当レフェリーがいません</td>
            </tr>
          @else
            @foreach ($refs as $ref)
              <tr>
                <td class="text-center">{{ $ref->surname_kanji }} {{ $ref->name_kanji }}</td>
                <td class="text-center">{{ $ref->registration_number }}</td>
                <td class="text-center">{{ $ref->organization->short_name }}</td>
                <td class="text-center">{{ $ref->license->name }}</td>
                <td class="text-center">
                  @if ($ref->user)
                    <i class="fa-solid fa-circle-check text-success" title="紐付けあり"></i>
                  @else
                    <i class="fa-solid fa-xmark text-danger" title="紐付けなし"></i>
                  @endif
                </td>
                <td class="text-center">
                  <a href="{{ route('admin.referees.edit', $ref->id) }}" title="詳細">
                    <i class="fa-solid fa-circle-info text-secondary"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>

      {{-- ページングがあるならここ（必要なら） --}}
      {{-- <div class="d-flex justify-content-center">
        {{ $refs->links() }}
      </div> --}}

    </div>
  </div>

@else
  {{-- データがない場合（インポート案内など） --}}
  <div class="row gx-5 d-flex justify-content-center table-responsive">
    <div class="col-md-8">
      @include('admin.referees.import')
    </div>
  </div>
@endif
@endsection
