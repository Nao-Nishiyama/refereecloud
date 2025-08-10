@extends('layouts.app')

@section('title', 'Create Post')
    
@section('content')
    <form action="{{ route('post.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    
    {{-- categories --}}
    <div class="mb-3">
        <label for="category" class="form-label d-block fw-bold">
            Category <span class="text-muted fw-normal">(Up to 3)</span>
        </label>

        @foreach ($all_categories as $category)
            <div class="form-check form-check-inline">
                <input type="checkbox" name="category[]" id="{{ $category->name}}" class="form-check-input" value="{{ $category->id}}">

                <label for="{{ $category->name}}" class="form-check-label">{{ $category->name }}</label>
            </div>
        @endforeach
        @error('category')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- description --}}
    <div class="mb-3">
        <label for="description" class="form-label fw-bold">Description</label>

        <textarea name="description" id="description" rows="3" class="form-control" placeholder="What's on your mind?">{{ old('description') }}</textarea>
        @error('description')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    {{-- image --}}
    <div class="mb-4">
        <label for="image" class="form-label fw-bold">Image</label>
        <input type="file" name="image" id="image" class="form-control" aria-describedby="image-info">
        <div class="form-text" id="image-info">
            The acceptable formats are jpeg, jpg, png and gif only. <br>
            Max file is 1048kb.
        </div>
        @error('image')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary px-5">Post</button>
    </form>
@endsection