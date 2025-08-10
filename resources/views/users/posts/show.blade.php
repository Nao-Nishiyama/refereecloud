@extends('layouts.app')

@section('title', 'Show Post')
    
@section('content')
    <style>
        .col-4{
            overflow-y: scroll;
        }
        .card-body{
            position: absolute;
            top:65px;
        }
    </style>

    <div class="row border shadow">
        {{-- post image --}}
        <div class="col p-0 border-end">
            <img src="{{ $post->image }}" alt="post id {{ $post->id }}" class="w-100">
        </div>

        {{-- post details --}}
        <div class="col-4 px-0 bg-white">
            <div class="card border-0">
                {{-- header --}}
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        {{-- avatar --}}
                        <div class="col-auto">
                            <a href="{{ route('profile.show', $post->user->id) }}">
                                @if ($post->user->avatar) {{-- $post comes from home.blade.php--}}
                                    <img src="{{ $post->user->avatar }}" alt="{{ $post->user->name }}" class="rounded-circle avatar-sm">
                                @else
                                    <i class="fa-solid fa-circle-user text-secondary icon-sm"></i>
                                @endif
                            </a>
                        </div>
                                        
                        {{-- name --}}
                        <div class="col">
                            <a href="{{ route('profile.show', $post->user->id) }}" class="text-decoration-none text-dark">
                                {{ $post->user->name }}
                            </a>
                        </div>

                        {{-- action button --}}
                        <div class="col-auto">
                            {{-- If you are the pwner of the post, youcan edit or delete this post --}}
                            @if ( Auth::user()->id === $post->user->id )
                                <div class="dropdown">
                                    <button class="btn btn-sm-shadow-none" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-ellipsis"></i>
                                    </button>

                                    <div class="dropdown-menu">
                                        {{-- edit --}}
                                        <a href="{{ route('post.edit', $post->id)}}" class="dropdown-item">
                                            <i class="fa-regular fa-pen-to-square"></i> Edit
                                        </a>
                
                                        {{-- delete --}}
                                        <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#delete-post-{{ $post->id }}"> {{-- # is used for id --}}
                                            <i class="fa-regular fa-trash-can"></i> Delete
                                        </button>
                                    </div>
                                    {{-- include delete modal post --}}
                                    @include('users.posts.contents.modals.delete')
                                </div>
                            @else
                                @if ($post->user->isFollowed())
                                    {{-- Unfollow User --}}
                                    <form action="{{ route('follow.destroy', $post->user->id)}}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="border-0 bg-transparent p-0 text-secondary">Following</button>
                                    </form>                                    
                                @else
                                    {{-- Follow User --}}
                                    <form action="{{ route('follow.store', $post->user->id) }}" method="post">
                                        @csrf

                                        <button type="submit" class="border-0 bg-transparent p-0 text-primary">Follow</button>
                                    </form>
                                @endif
                            @endif
                        </div>

                    </div>
                </div>

                {{-- body --}}
                <div class="card-body w-100 bg-white">
                    {{-- heart button + # of likes + categories --}}
                    <div class="row align-items center">
                        {{-- heart button --}}
                        <div class="col-auto">
                            @if ($post->isLiked())
                                {{-- Unlike the post --}}
                                <form action="{{ route('like.destroy', $post->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm shadow-none p-0">
                                        <i class="fa-solid fa-heart text-danger"></i>
                                    </button>
                                </form>
                            @else
                                {{-- Like the post --}}
                                <form action="{{ route('like.store', $post->id) }}" method="post">
                                    @csrf
                                    <button type="submit" class="btn btn-sm shadow-none p-0">
                                        <i class="fa-regular fa-heart"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- no. of likes --}}
                        <div class="col-auto px-0">
                            <button class="btn btn-sm shadow-none p-0 text-center" data-bs-toggle="modal" data-bs-target="#show-likes-{{ $post->id }}">
                                {{ $post->likes->count() }}
                            </button>
                            @include('users.posts.contents.modals.like')
                        </div>

                        {{-- categories --}}
                        <div class="col text-end">
                            @forelse ( $post->categoryPost as $category_post)
                                <span class="badge bg-secondary bg-opacity-50">
                                    {{ $category_post->category->name }}
                                </span>
                            @empty
                                <div class="badge bg-dark text-wrap">Uncategorized</div>
                            @endforelse
                        </div>
                    </div>

                    {{-- owner + description --}}
                    <a href="{{ route('profile.show', $post->user->id) }}" class="text-decoration-none text-dark fw-bold">
                        {{ $post->user->name }}
                    </a>
                    &nbsp;
                    <p class="fw-light d-inline">{{ $post->description }}</p>
                    <p class="text-uppercase text-muted xsmall">{{ date('M d, Y', strtotime($post->created_at)) }}</p>
                    
                    {{-- comments --}}
                    <div class="mt-4">
                        <form action="{{ route('comment.store', $post->id) }}" method="post">
                            @csrf

                            <div class="input-group">
                                <textarea name="comment_body{{ $post->id }}" rows="1" class="form-control form-control-sm" placeholder="Add a comment...">{{ old('comment_body' . $post->id) }}</textarea>
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Post</button>
                            </div>
                            {{-- ERROR --}}
                            @error('comment_body' . $post->id)
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </form>
                    </div>

                    {{-- Show all comments --}}
                    @if ($post->comments->isNotEmpty())
                    <ul class="list-group mt-2">
                        @foreach ($post->comments as $comment)
                            <li class="list-group-item border-0 bg-white p-0 mb-2">
                                <a href="{{ route('profile.show', $comment->user_id) }}" class="text-decoration-none text-dark fw-bold">{{ $comment->user->name }}</a>
                                &nbsp;
                                <p class="d-inline fw-light">{{ $comment->body }}</p>

                                <form action="{{ route('comment.destroy', $comment->id)}}" method="post">
                                    @csrf
                                    @method('DELETE')

                                    <span class="text-uppercase text-muted xsmall">{{ date('M d, Y', strtotime($comment->created_at) )}}</span>

                                    {{-- If the Auth::user() is the OWNER of the COMMENT, show a delete button --}}
                                    @if (Auth::user()->id === $comment->user->id)
                                        &middot;
                                        <button type="submit" class="border-0 bg-transparent text-danger p-0 xsmall">Delete</button>
                                    @endif
                                </form>
                            </li>
                        @endforeach
                    </ul>
                        
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection