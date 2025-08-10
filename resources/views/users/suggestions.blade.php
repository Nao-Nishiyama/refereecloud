@extends('layouts.app')

@section('title', 'Suggestions')
    
@section('content')
<div class="row justify-content-center">
    <div class="col-6 shadow-sm rounded p-4">
        <h3 class="text-muted mb-0 text-center">suggested</h3>
        <div class="row">
            @if ($suggested_users)
                <div class="row">

                    @foreach ($suggested_users as $user)
                        <div class="row align-items-center mb-3">
                            {{-- avatar --}}
                            <div class="col-auto">
                                <a href="{{ route('profile.show', $user->id) }}">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle avatar-md">
                                    @else
                                        <i class="fa-solid fa-circle-user text-secondary icon-md"></i>
                                    @endif
                                </a>
                            </div>

                            {{-- name --}}
                            <div class="col ps-0 text-truncate">
                                <a href="{{ route('profile.show', $user->id) }}" class="text-decoration-none text-dark fw-bold">
                                    {{ $user->name }}
                                </a>
                                <p class="text-muted small">
                                    {{$user->email}}</br>
                                    {{ $user->followers->count() }} {{$user->followers->count() == 1 ? 'follower' : 'followers' }}
                                </p>
                            </div>

                            {{-- follow --}}
                            <div class="col-auto">
                                <form action="{{ route('follow.store', $user->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Follow
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
            @endif
        </div>

    </div>
</div>
@endsection