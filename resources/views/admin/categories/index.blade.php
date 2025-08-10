@extends('layouts.app')

@section('title', 'Admin Categories')
    

@section('content')
    <form action="{{ route('admin.categories.store') }}" method="post" class="row gx-2 mb-4">
        @csrf
            <div class="col-4">
                <input type="text" class="form-control" name="name" placeholder="Add a category..." autofocus>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add</button>
            </div>
            @error('name')
                <p class="text-danger small">{{ $message }}</p>
            @enderror
    </form>
    <div class="row">
        <div class="col-7">
            <table class="table table-hover align-middle text-center bg-white border text-secondary">
                <thead class="small table-warning text-primary">
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>COUNT</th>
                        <th>LAST UPDATED</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($all_categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td class="text-dark">{{ $category->name }}</td>
                            <td>{{ $category->categoryPost->count()}}</td>
                            <td>{{ date('M d, Y', strtotime($category->updated_at)) }}</td>
                            <td>
                                <button class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#edit-category-{{ $category->id }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                {{-- Include Category Modal --}}
                                @include('admin.categories.modal.action')

                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete-category-{{ $category->id }}">
                                    <i class="fa-solid fa-trash-can text-danger"></i>
                                </button>
                                {{-- Include Category Modal --}}
                                @include('admin.categories.modal.action')
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="lead text-muted text-center">
                                No categories found.
                            </td>
                        </tr>
                    @endforelse
                        <tr>
                            <td></td>
                            <td class="text-dark">
                                Uncategorized
                                <p class="xsmall mb-0 text-muted">Hidden posts are not included.</p>
                            </td>
                            <td>{{ $uncategorized_count}}</td>
                            <td></td>
                            <td></td>
                        </tr>
                </tbody>
            </table>
            {{ $all_categories->links() }}
        </div>
    </div>
@endsection