@extends('layouts.app')

@section('title', 'Home')
    

@section('content')
    <div class="row gx-5">
        {{-- POSTS --}}
        <div class="col-8">
            @forelse ($home_posts as $post)
                <div class="card mb-4">
                    {{-- title --}}
                        @include('users.posts.contents.title') {{-- $post can be used in title.blade.php --}}
                    {{-- body --}}
                        @include('users.posts.contents.body')
                </div>
            @empty
                {{-- If the site doesn't have any posts yet. --}}
                <div class="text-center">
                    <h2>Share Photos</h2>
                    <p class="text-muted">When you share photo, they'll appear on your profile.</p>
                    <a href="{{ route('post.create')}}" class="text-decoration-none">
                        Share your first photo
                    </a>
                </div>
            @endforelse
        </div>

        {{-- PROFILE OVERVIEW + SUGGESTIONS --}}
        <div class="col-4">
            {{-- PROFILE OVERVIEW --}}
            <div class="row align-item-center mb-5 shadow-sm rounded p-3">
                {{-- avatar --}}
                <div class="col-auto">
                    <a href="{{ route('profile.show', Auth::user()->id) }}">
                        @if ( Auth::user()->avatar )
                            <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="rounded-circle avatar-md"></a>
                        @else
                            <i class="fa-solid fa-circle-user text-secondary icon-md"></i>                            
                        @endif
                </div>

                {{-- name + email --}}
                <div class="col ps-0">
                    <a href="{{ route('profile.show', Auth::user()->id )}}" class="text-decoration-none text-dark fw-bold">{{Auth::user()->name}}</a>
                    <p class="text-muted">{{ Auth::user()->email }}</p>
                </div>
            </div>

            {{-- SUGGESTIONS --}}
            @if ($suggested_users)
                <div class="row">
                    <div class="col-auto">
                        <p class="fw-bold text-secondary">Suggestions For You</p>
                    </div>

                    <div class="col text-end">
                        {{-- suggestion.blade.php --}}
                        <a href="{{ route('suggestions')}}" class="fw-bold text-dark text-decoration-none">See All</a>
                    </div>
                </div>

                    @foreach ($suggested_users as $user)
                        <div class="row align-items-center mb-3">
                            {{-- avatar --}}
                            <div class="col-auto">
                                <a href="{{ route('profile.show', $user->id) }}">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle avatar-sm">
                                    @else
                                        <i class="fa-solid fa-circle-user text-secondary icon-sm"></i>
                                    @endif
                                </a>
                            </div>

                            {{-- name --}}
                            <div class="col ps-0 text-truncate">
                                <a href="{{ route('profile.show', $user->id) }}" class="text-decoration-none text-dark fw-bold">
                                    {{ $user->name }}
                                </a>
                            </div>

                            {{-- follow --}}
                            <div class="col-auto">
                                <form action="{{ route('follow.store', $user->id)}}" method="post">
                                    @csrf
                                    <button type="submit" class="border-0 bg-transparent p-0 text-primary btn-sm">
                                        Follow
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach
            @endif
        </div>
    </div>
@endsection
