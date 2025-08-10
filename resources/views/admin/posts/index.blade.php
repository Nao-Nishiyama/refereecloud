@extends('layouts.app')

@section('title', 'Admin Posts')
    

@section('content')
    <table class="table table-hover align-middle bg-white border text-secondary">
        <thead class="small table-primary text-dark">
            <tr>
                <th></th>
                <th></th>
                <th>CATEGORY</th>
                <th>OWNER</th>
                <th>CREATED_AT</th>
                <th>STATUS</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($all_posts as $post)
                <tr>
                    <td>{{ $post->id }}</td>
                    <td>
                        <img src="{{ $post->image }}" alt="post id {{ $post->id}}" class="image-lg">
                    </td>
                    <td>
                        @forelse ($post->categoryPost as $category_post)
                            <span class="badge bg-secondary bg-opacity-50">
                                {{ $category_post->category->name }}
                            </span>
                        @empty
                            <div class="badge bg-dark text-wrap">Uncategorized</div>
                        @endforelse
                    </td>
                    <td>{{ $post->user->name }}</td>
                    <td>{{ date('M d, Y', strtotime($post->created_at)) }}</td>
                    <td>
                        {{-- $user->trashed() returns TRUE if the user was soft deleted --}}
                        @if ( $post->trashed() )
                            <i class="fa-solid fa-circle-minus text-secondary"></i>&nbsp; Hidden
                        @else
                            <i class="fa-solid fa-circle text-primary"></i>&nbsp; Visible
                        @endif
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis"></i>
                            </button>

                            <div class="dropdown-menu">
                                @if ( $post->trashed() )
                                    <button class="dropdown-item text-primary" data-bs-toggle="modal" data-bs-target="#unhide-post-{{ $post->id }}">
                                        <i class="fa-solid fa-eye"></i> Unhide Post {{ $post->id }}
                                    </button>
                                @else
                                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#hide-post-{{ $post->id }}">
                                        <i class="fa-solid fa-eye-slash"></i> Hide Post {{ $post->id }}
                                    </button>                                @endif
                            </div>
                        </div>
                        {{-- Include Hide Post Modal --}}
                        @include('admin.posts.modal.status')
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $all_posts->links() }}
@endsection