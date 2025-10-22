@extends('layouts.app')

@section('title', '大会設定')
    
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <form action="{{ route('admin.competitions.store') }}" method="post" enctype="multipart/form-data">
            @csrf

            {{-- 大会名 --}}
            <div class="row mb-2">
                <label for="name" class="text-end col-form-label col-4">大会名</label>
                <div class="col-8">
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name')}}">
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 種別 --}}
            <div class="row mb-2">
                <label for="type_id" class="text-end col-4 col-form-label">種別</label>
                <div class="col-8">
                    <select name="type_id" id="type_id" class="form-select">
                        <option value="">選択してください</option>
                        @foreach($all_types as $type)
                        <option value="{{ $type->id }}" {{ old('type_id')==$type->id?'selected':'' }}>
                            {{ $type->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('type_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 募集日程 --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">募集日程</label>
                <div class="col-auto">
                    <input type="date" name="start_day" class="form-control text-center" value="{{ old('start_day') }}">
                    @error('start_day')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-auto">〜</div>
                <div class="col-auto">
                    <input type="date" name="end_day" class="form-control text-center" value="{{ old('end_day') }}">
                    @error('end_day')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 開催都市 --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">開催地</label>
                <div class="col-8">
                    <input type="text" name="city" class="form-control text-center" value="{{ old('city') }}">
                    @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 会場 --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">会場</label>
                <div class="col-8">
                    <input type="text" name="venue" class="form-control text-center" value="{{ old('venue') }}">
                    @error('venue')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 申込締切 --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">申込締切</label>
                <div class="col-8">
                    <input type="date" name="application_deadline" class="form-control text-center" value="{{ old('application_deadline') }}">
                    @error('application_deadline')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Organizer メッセージ --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">備考（ユーザー向け）</label>
                <div class="col-8">
                    <textarea name="organizer_message" class="form-control" rows="4">{{ old('organizer_message') }}</textarea>
                    @error('organizer_message')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- 管理者メモ --}}
            <div class="row mb-2">
                <label class="col-form-label col-4 text-end">メモ（管理者のみ）</label>
                <div class="col-8">
                    <textarea name="admin_private_note" class="form-control" rows="4">{{ old('admin_private_note') }}</textarea>
                    @error('admin_private_note')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="row justify-content-center">
                <button type="submit" class="btn btn-outline-success mt-3" style="width:20%">保存</button>
            </div>
        </form>
    </div>
</div>
@endsection
