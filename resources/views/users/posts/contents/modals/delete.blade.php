<div class="modal fade" id="delete-post-{{ $post->id }}"> {{-- definitely need $post->id / this id will be called in the title.blade.php --}}
    <div class="modal-dialog"> {{-- dialog --}}
        <div class="modal-content border-danger"> {{-- content --}}
            <div class="modal-header border-danger"> {{-- header --}}
                <div class="h5 modal-title text-danger"> {{-- title --}}
                    <i class="fa-solid fa-circle-exclamation"></i> Delete Post
                </div>
            </div>
            <div class="modal-body"> {{-- body --}}
                <p>Are you sure you want to delete this post?</p>
                <div class="mt-3">
                    <img src="{{ $post->image }}" alt="post id {{ $post->id }}" class="image-lg">
                    <p class="mt-1 text-muted">{{ $post->description }}</p>
                </div>
            </div>
            <div class="modal-footer border-0"> {{-- footer --}}
                <form action="{{ route('post.destroy', $post->id) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"> {{-- button type because no need of action --}}
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>