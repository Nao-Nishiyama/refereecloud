@extends('layouts.app')

@section('title', 'Nexus Control')

@section('content')
    <div class="container">
        <h4 class="mb-3">活動情報</h4>

        {{-- 上段メトリクス --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card card-action shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="action-icon">
                            <i class="fa-solid fa-calendar-check fa-lg"></i>
                        </div>
                        <div>
                            <div class="small text-muted">今後の大会</div>
                            <div class="fs-4 fw-bold">{{ $upcomingCompetitions }}</div>
                        </div>
                    </div>
                    <a href="{{ route('competitions.show') }}" class="stretched-link"></a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card card-action shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 ">
                        <div class="action-icon">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div>
                            <div class="small text-muted">活動報告</div>
                            <div class="fs-4 fw-bold">{{ $user->reports->count(); }}</div>
                        </div>
                    </div>
                    <a href="{{ route('reports.show', $user->id) }}" class="stretched-link"></a>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="card card-action shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3 ">
                        <div class="action-icon">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                    <div>
                        <div class="small text-muted">アカウント情報</div>
                        <div class="fs-4 fw-bold">
                            @if ($user->referee)
                                @if ($user->referee->registration_number)
                                    {{ $user->referee->registration_number }}
                                @else
                                    <span class="fw-light text-muted fs-6">所属：{{ $user->referee->organization->short_name }}</span>
                                @endif
                            @else
                                <span class="text-muted fw-light fs-6">データ未連携</span>
                            @endif
                        </div>
                    </div>
                    </div>
                    <a href="{{ route('profile.show') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <hr>

        {{-- 管理者/委員用の追加カード --}}
        @canany(['admin','committee', 'chief'])
            <h6>大会管理（{{ $fiscalYear }}年度）</h6>
            <div class="row g-3 mb-4"> 
                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-list-ul"></i>
                            </div>
                            <div>
                                <div class="small text-muted">大会一覧</div>

                                @php
                                    $fyStart = \Carbon\Carbon::create($fiscalYear, 4, 1)->startOfDay();
                                    $fyEnd   = \Carbon\Carbon::create($fiscalYear + 1, 3, 31)->endOfDay();
                                    $countFy = \App\Models\Competition::whereBetween('start_day', [$fyStart, $fyEnd])->count();
                                @endphp

                                <div class="fs-4 fw-bold">{{ $countFy }}</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.competitions.show') }}" class="stretched-link"></a>
                    </div>
                </div>
                
                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                            <i class="fa-solid fa-diagram-project"></i>
                            </div>
                            <div>
                                <div class="text-muted">団体割当</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.nominations.capacities.show') }}" class="stretched-link"></a>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                            <div>
                                <div class="text-muted">新規作成</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.competitions.create') }}" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @canany(['admin', 'committee'])
                    <div class="col-12 col-md-4">
                        <div class="card card-action shadow-sm h-100">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="action-icon">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">参加希望者一覧</div>
                                    @php

                                    @endphp
                                     <div class="fs-4 fw-bold">{{ $ApplCount }}</div>
                                </div>
                            </div>
                            <a href="{{route('admin.applications')}}" class="stretched-link"></a>
                        </div>
                    </div>
                @endcanany

                @canany(['admin', 'committee'])
                    <div class="col-12 col-md-4">
                        <div class="card card-action shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3 ">
                                <div class="action-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">CSV 取込</div>
                                    <div class="fs-6">大会データの一括更新</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.competitions.import') }}" class="stretched-link"></a>
                        </div>
                    </div>
                @endcanany
            </div>
            <hr>

            <h6>審判員管理</h6>
            <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-people-group fa-lg"></i>
                            </div>        
                            <div>
                                <div class="small text-muted">所属団体の審判員</div>
                                    @if( $user->role_id === 3)
                                        <div>
                                            <span class="fs-6 fwbold">{{ $user->referee->organization->short_name }}：</span>
                                            <span class="fs-4 fw-bold">{{ $myOrgRefCount }}</span>
                                        </div>
                                    @elseif ($user->role_id === 2 )
                                        <div>
                                            <span class="fs-6 fwbold">{{ $user->referee->prefecture->name }}：</span>
                                            <span class="fs-4 fw-bold">{{ $RefCount }}</span>
                                        </div>
                                    @else
                                        <div>
                                           @if(!is_null($myOrgRefCount))
                                            <span class="fs-6 fwbold">{{ $user->referee->prefecture->name }}：</span>
                                            @endif
                                            <span class="fs-4 fw-bold">{{ $RefCount }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @canany(['admin','committee'])
                                <a href="{{ route('admin.referees.show') }}" class="stretched-link"></a>
                            @endcanany
                            @canany(['chief'])
                                <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="stretched-link"></a>
                            @endcanany
                        </div>
                    </div>

                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-table-list"></i>
                            </div>
                            <div>
                                <div class="small text-muted">活動報告一覧（{{ $fiscalYear }}年度）</div>
                                <div class="fs-4 fw-bold">{{ $ReportCount }}</div>
                            </div>
                        </div>
                        @canany(['admin','committee'])
                            <a href="{{ route('admin.referees.reports.show') }}" class="stretched-link"></a>
                        @endcanany
                        @canany(['chief'])
                            <a href="{{ route('admin.referees.reports.show',['organization' => optional($user->referee)->organization_id]) }}" class="stretched-link"></a>
                        @endcanany
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm h-100">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <div>
                                <div class="text-muted">新規申請（{{ $fiscalYear }}年度）</div>
                            </div>
                        </div>
                        @canany(['admin','committee','chief'])
                            <a href="{{ route('admin.referees.create') }}" class="stretched-link"></a>
                        @endcanany
                    </div>
                </div>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <div class="small text-muted">新規申請者一覧（{{ $fiscalYear }}年度）</div>
                                <div class="fs-4 fw-bold">{{ $pendingApplicants }}</div>
                            </div>
                        </div>
                        @canany(['admin','committee','chief'])
                            <a href="{{ route('admin.referees.approval', ['applicants' => 1]) }}" class="stretched-link"></a>
                        @endcanany
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card card-action shadow-sm">
                        <div class="card-body d-flex align-items-center gap-3 ">
                            <div class="action-icon">
                                <i class="fa-solid fa-database fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-muted">登録処理</div>
                            </div>
                        </div>
                        <a href="{{ route('admin.referees.approval') }}" class="stretched-link"></a>
                    </div>
                </div>

                @canany(['admin', 'committee'])
                    <div class="col-12 col-md-4">
                        <div class="card card-action shadow-sm">
                            <div class="card-body d-flex align-items-center gap-3 ">
                                <div class="action-icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="small text-muted">CSV 取込</div>
                                    <div class="fs-6">審判員データの一括更新</div>
                                </div>
                            </div>
                            <a href="{{ route('admin.referees.database') }}" class="stretched-link"></a>
                        </div>
                    </div>
                @endcanany
            </div>

            <hr>

       @endcanany
        
        <h6>大会情報（{{$fiscalYear}}）</h6>

        {{-- 直近の大会 --}}
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-trophy"></i> 直近の大会</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>大会名</th>
                            <th>種別</th>
                            <th>期間</th>
                            <th>開催地</th>
                            @canany(['admin','committee','chief'])
                                <th>管理</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($latestCompetitions as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td>{{ optional($c->type)->name ?? '-' }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($c->start_day)->format('n/j') }}
                                    〜
                                    {{ \Carbon\Carbon::parse($c->end_day)->format('n/j') }}
                                </td>
                                <td>{{ $c->city }}</td>
                                @canany(['admin','committee','chief'])
                                    <td>
                                        <a href="{{ route('admin.competitions.edit', $c->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">直近の大会はありません。</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ふわっとカード用（任意） --}}
    <style>
        .card-hover { transition: background 1.2s ease, box-shadow .2s ease, transform .2s ease; cursor:
            pointer; }
        .card-hover:hover {
            background: radial-gradient(1200px 400px at 10% -10%, rgba(9,126,242,.08), transparent),
            radial-gradient(800px 300px at 110% 120%, rgba(244,142,232,.08), transparent);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
        }
    </style>

{{-- ふわっとパステルのホバー＆追従グラデ --}}
    <style>
    .card-action {
        border: 1px solid #eff1f5;
        border-radius: 2rem !important;
        transition: background 0.6s ease, box-shadow .35s ease, transform .35s ease;
        position: relative;
        overflow: hidden; /* グラデがはみ出さないように */
        cursor: pointer;
    }

    /* ホバー位置に追従する放射状グラデ（--x, --y を JS で更新） */
    .card-action::before {
        content: "";
        position: absolute;
        inset: -40%;
        background:
        radial-gradient(600px circle at var(--x, 50%) var(--y, 50%),
            rgba(129, 140, 248, .14), rgba(236, 72, 153, .10), transparent 60%);
        opacity: 0;
        transition: opacity .35s ease;
        pointer-events: none;
        border-radius: inherit;
    }
    
    .card-action:hover {
        box-shadow: 0 .65rem 2rem rgba(16, 24, 40, 0.10);
        transform: translateY(-2px);
    }

    .card-action:hover::before { opacity: 1; }

    /* アイコン少し強調（任意） */
    .card-action .card-body > i,
    .card-action .card-body .fa-solid {
        font-size: 1.25rem;
        color: #149b31;
    }

    .action-icon {
        width: 44px; height: 44px;
        display: grid; place-items: center;
        border-radius: 50%;
        background: radial-gradient(140px circle at 30% 30%,
        rgba(59,130,246,.18), rgba(168,85,247,.12), rgba(236,72,153,.10));
        font-size: 1.1rem; color: #0d6efd;
        }
    </style>

    <script>
    // ホバー位置に合わせて --x, --y を更新
    document.querySelectorAll('.card-action').forEach(card => {
        card.addEventListener('mousemove', (e) => {
        const r = card.getBoundingClientRect();
        card.style.setProperty('--x', `${e.clientX - r.left}px`);
        card.style.setProperty('--y', `${e.clientY - r.top}px`);
        });
        // 触ってない時は中央に戻す
        card.addEventListener('mouseleave', () => {
        card.style.removeProperty('--x');
        card.style.removeProperty('--y');
        });
    });
    </script>

@endsection