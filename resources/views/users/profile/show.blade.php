@extends('layouts.app')

@section('title', "User's Info")
    

@section('content')
    <div class="row gx-5 d-flex justify-content-center table-responsive mt-3">
        <div class="col-6">
            @if ($user->referee)
            <div class="row mb-4 justify-content-center">                    
                <div class="col-auto">
                    <h2 class="display-6 mb-0">
                        {{ $user->referee->surname_kanji }} {{ $user->referee->name_kanji }}
                    </h2>
                </div>
                <div class="col-auto d-flex align-items-end">
                    {{ $user->referee->name }} {{ $user->referee->surname }}
                </div>
            </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('登録番号：')}}
                    </div>
                    <div class="col-8">
                        {{ $user->referee->registration_number }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('生年月日：')}}
                    </div>
                    <div class="col-8">
                        {{ \Carbon\Carbon::parse($user->referee->birth_date)->format('Y 年 m 月 d 日') }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('年齢：')}}
                    </div>
                    <div class="col-8">
                        {{ \Carbon\Carbon::parse($user->referee->birth_date)->age }}
                        {{ __('歳（本日時点）') }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('所属：')}}
                    </div>
                    <div class="col-8">
                        {{ $user->referee->prefecture->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('資格：')}}
                    </div>
                    <div class="col-8">
                        {{ $user->referee->license->name }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('カテゴリー等：')}}
                    </div>
                    <div class="col-8">
                        @foreach ( $user->referee->categories as $category )
                            {{-- <span class="me-2"> --}}
                                {{ $category->name }}
                                    @if (! $loop->last)
                                        <span class="me-2">{{ __(', ')}}</span>
                                    @endif
                            {{-- </span> --}}
                        @endforeach
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('mrsメンバーID：')}}
                    </div>
                    <div class="col-8">
                        {{ $user->referee->mrs_member_id }}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('ステータス：')}}
                    </div>
                    <div class="col-8">
                        @if ( $user->role_id == 1 )
                            {{__('管理者')}}
                        @elseif ( $user->role_id == 2 )
                            {{__('審判委員会三役')}}
                        @elseif ( $user->role_id == 3 )
                            {{__('組織加盟団体審判委員長')}}
                        @else
                            {{__('一般ユーザー')}}
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4 text-end">
                        {{ __('備考：')}}
                    </div>
                    <div class="col-8">
                        {{ $user->referee->remarks }}
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">{{__('編集')}}</a>
                    <a href="{{route('index')}}" class="btn btn-dark ms-2" style="width: 10%">戻る</a>
                </div>
            @else
                {{__('データベース未連携')}}
            @endif
        </div>
    </div>
@endsection
