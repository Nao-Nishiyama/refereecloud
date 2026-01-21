@extends('layouts.app')

@section('title', '審判員管理')
    
@section('content')
@php
    
@endphp
<style>
  /* ベースのカード */
  .card-hover {
    position: relative;
    overflow: hidden;              /* はみ出る光を切る */
    transition: box-shadow .5s ease, color .4s ease;
    cursor: pointer;
    background: #fff;
  }

  /* 放射状パステルの柔らかいグラデーションを重ねる */
  .card-hover::before {
    content: "";
    position: absolute;
    inset: -20%;                   /* ふちのにじみ用に少し大きめ */
    background:
      radial-gradient(120% 120% at 20% 20%,
        rgba(255, 255, 255, 0.28) 0%, rgba(244,142,232,0) 55%) ,  /* pink */
      radial-gradient(120% 120% at 80% 80%,
        rgba(147, 255, 187, 0.28) 0%, rgba(148,187,233,0) 55%) ,  /* blue */
      radial-gradient(120% 120% at 30% 80%,
        rgba(147, 255, 187, 0.28) 0%, rgba(255,214,165,0) 55%) ,  /* peach */
      radial-gradient(120% 120% at 85% 20%,
        rgba(147, 255, 187, 0.28) 0%, rgba(173,236,255,0) 55%);   /* mint */
    filter: blur(1px);              /* ぼかしでふわっと */
    opacity: 0;                     /* 初期は非表示 */
    transform: scale(1.06);         /* 少し大きくしてホバーで戻す */
    transition: opacity .9s ease, transform 1.4s ease;
    z-index: 0;                     /* テキストより下に */
    animation: drift 9s ease-in-out infinite paused; /* 初期は停止 */
    background-size: 140% 140%, 140% 140%, 140% 140%, 140% 140%;
    background-position: 0% 0%, 100% 100%, 15% 85%, 85% 15%;
  }

  /* ホバー/フォーカス時にグラデーションをふわっと出す */
  .card-hover:hover::before,
  .card-hover:focus-within::before {
    opacity: 1;
    transform: scale(1.0);
    animation-play-state: running;  /* ゆっくり漂う */
  }

  /* 影＆文字色も少しだけ変化 */
  .card-hover:hover,
  .card-hover:focus-within {
    box-shadow: 0 .75rem 1.5rem rgba(0,0,0,.12);
    color: #07480e;                 /* お好みで */
  }

  /* キーボード操作でも分かるようフォーカス枠 */
  .card-hover:focus-within { outline: 2px solid var(--bs-primary); outline-offset: 2px; }

  /* グラデーションをゆっくりドリフトさせる */
  @keyframes drift {
    0%   { background-position: 0% 0%,   100% 100%, 15% 85%, 85% 15%; }
    50%  { background-position: 8% 6%,   92% 94%,   20% 78%, 80% 22%; }
    100% { background-position: 0% 0%,   100% 100%, 15% 85%, 85% 15%; }
  }

  /* 動きに弱い人向けにアニメ無効化 */
  @media (prefers-reduced-motion: reduce) {
    .card-hover,
    .card-hover::before { transition: none !important; animation: none !important; }
  }
</style>

<div class="container">
    <a href="{{ route('admin.referees')}}" class="h2 text-decoration-none px-3">審判員管理</a>
    <div class="row mt-4">
        <div class="col-sm-4 mb-3 mb-sm-0">
          @if (Auth::user()->role_id === 3)
          <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="text-decoration-none text-reset">
          @else
          <a href="{{ route('admin.referees.show') }}" class="text-decoration-none text-reset">
              
          @endif
                <div class="card position-relative shadow card-hover">
                    <div class="card-body">
                        <h5 class="card-title">一覧</h5>
                        <h6 class="card-subtitle text-secondary mb-2 text-end">
                          @if (Auth::user()->role_id === 3)
                              {{Auth::user()->referee->organization->full_name}}
                          @else
                              京都府バレーボール協会
                          @endif
                        </h6>
                        <hr>
                        <p class="card-text mt-3">京都府バレーボール協会所属審判員のリストを表示します。</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4 mb-3 mb-sm-0">
            <a href="{{ route('admin.referees.reports.show') }}" class="card-link text-dark text-decoration-none">
                <div class="card position-relative shadow card-hover">
                    <div class="card-body">
                        <h5 class="card-title">活動報告</h5>
                        <h6 class="card-subtitle mb-2 text-body-secondary text-end">京都府バレーボール協会</h6>
                        <hr>
                        <p class="card-text mt-3">過去の年度における所属審判員の活動報告の提出状況を確認します。</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-4 mb-3 mb-sm-0">
            <a href="{{ route('admin.referees.create') }}" class="card-link text-decoration-none text-dark">
                <div class="card position-relative shadow card-hover">
                    <div class="card-body">
                        <h5 class="card-title">新規申請</h5>
                        <h6 class="card-subtitle mb-2 text-body-secondary text-end">京都府バレーボール協会</h6>
                        <hr>
                        <p class="card-text mt-3">今年度の新規資格認定希望者の申請をします。</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-sm-4 mb-3 mb-sm-0">
            <a href="{{ route('admin.referees.approval')}}" class="card-link text-decoration-none text-dark">
                <div class="card position-relative shadow card-hover">
                    <div class="card-body">
                        <h5 class="card-title">登録処理</h5>
                        <h6 class="card-subtitle mb-2 text-body-secondary text-end">京都府バレーボール協会</h6>
                        <hr>
                        <p class="card-text mt-3">今年度の新規登録・抹消の処理を行います。</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection