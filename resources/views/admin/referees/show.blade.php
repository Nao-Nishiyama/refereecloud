@extends('layouts.app')

@section('title', '審判員一覧')
    

@section('content')
    @php
    // 一覧
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

    @if ($allRefs->isNotEmpty())
    @if (Auth::user()->role_id === 1 || Auth::user()->role_id === 2)
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        <div class="btn-group mb-3 col-3" role="group">
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
        <div class="col-7"></div>
    </div>        
    @endif
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        <div class="col-2">
            <h6 class="mt-4">資格</h6>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center position-relative @class(['active' => empty($licId)])">
                    <span>すべて</span>
                    <span>{{ $countsByLic->sum() }}</span>
                    @canany(['admin','committee'])
                        <a href="{{ route('admin.referees.show') }}" class="stretched-link"></a>
                    @endcanany
                    @canany(['chief'])
                        <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="stretched-link"></a>
                    @endcanany
                </li>

            @foreach ($lics as $lic)
                <li class="list-group-item d-flex justify-content-between align-items-center position-relative @class(['active' => $licId === $lic->id])">
                <span>{{ $lic->name }}</span>
                <span>{{ $countsByLic[$lic->id] ?? 0 }}</span>
                @canany(['admin','committee'])
                    <a href="{{ route('admin.referees.show', ['license' => $lic->id]) }}" class="stretched-link"></a>
                @endcanany
                @canany(['chief'])
                    <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id,'license' => $lic->id]) }}" class="stretched-link"></a>
                @endcanany
                </li>
            @endforeach
            </ul>
        </div>
        @if (Auth::user()->role_id === 1 || Auth::user()->role_id === 2)
        <div class="col-2">
            <h6 class="mt-4">団体</h6>
            <ul class="list-group">
                <li class="list-group-item d-flex justify-content-between align-items-center position-relative @class(['active' => empty($orgId)])">
                    <span>すべて</span>
                    <span>{{ $countsByOrg->sum() }}</span>
                    <a href="{{ route('admin.referees.show') }}" class="stretched-link text-decoration-none"></a>
                </li>

            @foreach ($orgs as $org)
                <li class="list-group-item d-flex justify-content-between align-items-center position-relative @class(['active' => $orgId === $org->id])">
                <span>{{ $org->short_name }}</span>
                <span>{{ $countsByOrg[$org->id] ?? 0 }}</span>
                <a href="{{ route('admin.referees.show', ['organization' => $org->id]) }}"
                    class="stretched-link text-decoration-none"></a>
                </li>
            @endforeach
            </ul>
        </div>
        @endif

        @if (Auth::user()->role_id === 3)
        <div class="col-8">
        @else
        <div class="col-6">
        @endif
            <div class="d-flex justify-content-between align-items-center mb-2">
                @if ($licId)
                <div>
                    フィルタ：{{ optional($lics->firstWhere('id', $licId))->name }} 級
                        @if (Auth::user()->role_id === 3)
                            <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="ms-2 small">解除</a>
                        @else
                            <a href="{{ route('admin.referees.show') }}" class="ms-2 small">解除</a>
                        @endif
                        <div class="text-muted small">
                            {{ $refsByLic->count() }} 件
                        </div>
                    </div>
                @elseif ($orgId)
                <div>
                    フィルタ：{{ optional($orgs->firstWhere('id', $orgId))->full_name }}
                        @if (Auth::user()->role_id === 3)
                            <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="ms-2 small">解除</a>
                        @else
                            <a href="{{ route('admin.referees.show') }}" class="ms-2 small">解除</a>
                        @endif
                        <div class="text-muted small">
                            {{ $refsByOrg->count() }} 件
                        </div>
                </div>
                @else
                フィルタ：なし
                @endif                    
            </div>
            <table class="table align-middle table-hover bg-white border text secondary text-center shadow">
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
                <tbody>
                    @if ($refs->count() == 0)
                        <tr><td colspan="7">該当レフェリーがいません</td></tr>
                    @endif
                    @foreach ($refs as $ref)
                        <tr>
                            <td>{{ $ref->surname_kanji }} {{ $ref->name_kanji }}</td>
                            <td>{{ $ref->registration_number }}</td>
                            <td>{{ $ref->organization->short_name }}</td>
                            <td>{{ $ref->license->name }}</td>
                            <td>
                                @if ($ref->user)
                                <i class="fa-solid fa-circle-check text-success"></i>
                                @else
                                <i class="fa-solid fa-xmark text-danger"></i>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.referees.edit', $ref->id) }}">
                                    <i class="fa-solid fa-circle-info text-secondary"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
        @else
            <div class="row gx-5 d-flex justify-content-center table-responsive">
                <div class="col-md-8">
                    @include('admin.referees.import')
                </div>
            </div>
        @endif

@endsection