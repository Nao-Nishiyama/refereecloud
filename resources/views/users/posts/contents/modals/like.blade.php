<div class="modal fade" id="show-likes-{{ $post->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-success">
            <div class="modal-header border-success justify-content-center">
                <div class="h5 modal-title text-dark">
                    <i class="fa-solid fa-heart icon-sm text-danger"></i> Likes
                </div>
            </div>
            <div class="modal-body">
                @forelse ($all_likes as $like)
                    @if ($like->post_id === $post->id)
                    <div class="row justify-content-center mb-2">
                        <div class="col-auto">
                            <a href="{{ route('profile.show', $like->user->id) }}" class="text-decoration-none text-secondary">
                                @if ($like->user->avatar)
                                    <img src="{{ $like->user->avatar }}" alt="{{ $like->user->name }}" class="rounded-circle avatar-sm">
                                @else
                                    <i class="fa-solid fa-circle-user text-secondary icon-sm"></i>
                                @endif
                                &nbsp;
                                {{ $like->user->name }}
                            </a>
                        </div>
                    </div>
                    @endif
                @empty
                    No likes.
                @endforelse
            </div>
        </div>
    </div>
</div>