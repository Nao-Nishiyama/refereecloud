@extends('layouts.app')

@section('title', "レフェリー情報編集")

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <table class="table table-border-bottom">
                <tbody>
                    <tr>
                        <th scope="row">登録番号</th>
                        <td>{{ $referee->registration_number }}</td>
                    </tr>
                    <tr>
                        <th scope="row">名前</th>
                        <td>{{ $referee->surname_kanji }} {{ $referee->name_kanji }}</td>
                    </tr>
                    <tr>
                        <th scope="row">カナ</th>
                        <td>{{ $referee->surname_kana }} {{ $referee->name_kana }}</td>
                    </tr>
                    <tr>
                        <th scope="row">英字</th>
                        <td>{{ $referee->surname }}, {{ $referee->name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">所属</th>
                        <td>{{ $referee->organization->full_name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">メール</th>
                        <td>
                            @if ($referee->user)
                                {{ $referee->user->email }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">資格</th>
                        <td>{{ $referee->license->name}}</td>
                    </tr>
                    <tr>
                        <th scope="row">ステータス</th>
                        <td>
                            @if ($referee->user)
                                @if ($referee->user->role_id == 1)
                                    {{__('管理者')}}
                                @elseif ($referee->user->role_id == 2)
                                    {{__('三役')}}
                                @elseif ($referee->user->role_id == 3)
                                    {{__('組織加盟団体審判長')}}
                                @else
                                    {{__('一般ユーザー')}}
                                @endif
                            @else
                            {{ __('一般ユーザー')}}
                            @endif
                            <span class="ms-3">
                                @if ( $referee->user )
                                @if ($referee->user->id != Auth::user()->id )
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>

                                        <div class="dropdown-menu">
                                            @if ( $referee->user->role_id == 1 )
                                                @include('admin.referees.status.committee')
                                                @include('admin.referees.status.chief')
                                                @include('admin.referees.status.publicuser')
                                            @elseif( $referee->user->role_id == 2)
                                                @include('admin.referees.status.admin')
                                                @include('admin.referees.status.chief')
                                                @include('admin.referees.status.publicuser')
                                            @elseif( $referee->user->role_id == 3)
                                                @include('admin.referees.status.admin')
                                                @include('admin.referees.status.committee')
                                                @include('admin.referees.status.publicuser')
                                            @else
                                                @include('admin.referees.status.admin')
                                                @include('admin.referees.status.committee')
                                                @include('admin.referees.status.chief')
                                            @endif
                                        </div>
                                    </div>
                                    @include('admin.referees.modal.status')
                                    @endif
                                @endif
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="container">
                <a href="{{ route('admin.profiles.edit', $referee->id)}}" class="btn btn-outline-warning">編集</a>
                @if (Auth::user()->role_id === 3)
                    <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}" class="btn btn-outline-dark">一覧へ戻る</a>
                @else
                    <a href="{{ route('admin.referees.show') }}" class="btn btn-outline-dark">一覧</a>
                @endif
            </div>
        </div>
        <div class="col-md-4 mt-4">
            <div class="container">
                @if ( $referee->user )
                    <span>ユーザーとレフェリーは紐付けされています</span>
                        <form method="POST" action="{{ route('admin.referees.detach-user', $referee->id) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-outline-danger btn-sm">紐づけ解除</button>
                        </form>                    
                @else
                    <h5>レフェリーにユーザーを紐づけ</h5>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header">選択中のレフェリー</div>
                        <div class="card-body">
                        <div><strong>カナ：</strong>{{ $referee->surname_kana }} {{ $referee->name_kana }}</div>
                        <div><strong>氏名：</strong>{{ $referee->surname }} {{ $referee->name }}</div>
                        <div><strong>登録番号：</strong>{{ $referee->registration_number }}</div>
                        </div>
                    </div>

                    @if ($matched)
                        {{-- かな一致のユーザーが見つかった場合 --}}
                        <div class="alert alert-info">かな一致のユーザーが見つかりました。</div>

                        <table class="table table-bordered table-sm">
                        <thead><tr><th>かな</th><th>操作</th></tr></thead>
                        <tbody>
                            <tr>
                            <td>{{ $matched->surname_kana }} {{ $matched->name_kana }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.referees.attach-user', $referee->id) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="user_id" value="{{ $matched->id }}">
                                <button class="btn btn-primary btn-sm">このユーザーに紐づける</button>
                                </form>
                            </td>
                            </tr>
                        </tbody>
                        </table>

                        <hr>
                        <p class="text-muted">一致しない場合は下の検索で探してください。</p>
                    @endif

                    {{-- 検索フォーム（一致が無いときは必須、一致があっても別候補を探せる） --}}
                    <form method="GET" class="mb-3" action="#{{-- route('admin.referees.link-user', $referee) --}}">
                        <div class="input-group" style="max-width: 420px;">
                        <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="カタカナ でユーザー検索">
                        <button class="btn btn-outline-secondary">検索</button>
                        </div>
                    </form>

                    @if (!$matched && !$q)
                        <div class="text-muted">かな一致のユーザーが見つかりません。検索して候補を表示してください。</div>
                    @endif

                    @if ($candidates)
                        <table class="table table-bordered table-sm">
                        <thead><tr><th>かな</th><th>操作</th></tr></thead>
                        <tbody>
                            @forelse ($candidates as $u)
                            <tr>
                                <td>{{ $u->surname_kana }} {{ $u->name_kana }}</td>
                                <td>
                                @if (!$referee->user)
                                <form method="POST" action="{{ route('admin.referees.attach-user', $referee) }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $u->id }}">
                                    <button class="btn btn-primary btn-sm">このユーザーに紐づける</button>
                                </form>
                                @else
                                <form method="POST" action="{{-- route('admin.referees.detach-user', $referee) --}}">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm">紐づけ解除</button>
                                </form>
                                @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-muted">該当ユーザーが見つかりません。</td></tr>
                            @endforelse
                        </tbody>
                        </table>
                        {{ $candidates->links() }}
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection