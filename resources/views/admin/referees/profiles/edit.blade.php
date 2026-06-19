@extends('layouts.app')

@section('title', "Edit User's Info")
    

@section('content')
    <div class="row justify-content-center">
        <div class="col-8">
            <form action="{{ route('admin.profiles.update', $ref->id) }}" method="post" class="bg-white shadow rounded-3 p-5" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <h2 class="h3 mb-3 fw-light text-muted">登録情報更新</h2>
                {{-- name --}}
                <div class="mb-3">
                    <label for="surname_kanji" class="form-label">氏</label>
                    <input type="text" name="surname_kanji" id="surname_kanji" value="{{ old('surname_kanji', $ref->surname_kanji) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('surname_kanji')
                    <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_kanji" class="form-label">名</label>
                    <input type="text" name="name_kanji" id="name_kanji" value="{{ old('name_kanji', $ref->name_kanji) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('name_kanji')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="surname_kana" class="form-label">セイ</label>
                    <input type="text" name="surname_kana" id="surname_kana" value="{{ old('surname_kana', $ref->surname_kana) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('surname_kana')
                    <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name_kana" class="form-label">メイ</label>
                    <input type="text" name="name_kana" id="name_kana" value="{{ old('name_kana', $ref->name_kana) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('name_kana')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="surname" class="form-label">Surname</label>
                    <input type="text" name="surname" id="surname" value="{{ old('surname', $ref->surname) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('surname')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $ref->name) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('name')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 登録番号 --}}
                <div class="mb-3">
                    <label for="registration_number" class="form-label">登録番号</label>
                    <input type="string" name="registration_number" id="registration_number" value="{{ old('registration_number', $ref->registration_number) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('registration_number')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 生年月日 --}}
                <div class="mb-3">
                    <label for="birth_date" class="form-label">生年月日</label>
                    <input type="date" name="birth_date" id="birth_date" value="{{ old('birth_date', $ref->birth_date) }}" class="form-control" autofocus>
                    {{-- error --}}
                    @error('birth_date')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- 性別 --}}
                <div class="mb-3">
                    <label class="form-label">性別</label>
                    <select name="gender" class="form-select">
                        <option value="">選択してください</option>

                        <option value="1" @selected((string) old('gender', $ref->gender) === '1')>
                            男性
                        </option>

                        <option value="2" @selected((string) old('gender', $ref->gender) === '2')>
                            女性
                        </option>
                    </select>
                </div>
                {{-- 都道府県 --}}
                <div class="mb-3">
                    <label for="prefecture_id" class="form-label">都道府県</label>
                    <select name="prefecture_id" id="prefecture_id" class="form-control">
                        <option value="{{ old('prefecture_id', $ref->prefecture->id)}}" hidden>{{ $ref->prefecture->name}}</option>
                        @foreach ($prefectures as $prefecture)
                            <option value="{{ $prefecture->id }}">{{ $prefecture->name }}</option>
                        @endforeach
                    </select>

                    {{-- error --}}
                    @error('prefecture_id')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="organization_id" class="form-label">団体</label>
                    <select name="organization_id" id="organization_id" class="form-control">
                        <option value="{{ old('organization_id', $ref->organization->id)}}" hidden>{{ $ref->organization->short_name}}</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}">{{ $organization->short_name }}</option>
                        @endforeach
                    </select>

                    {{-- error --}}
                    @error('prefecture_id')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 資格 --}}
                <div class="mb-3">
                    <label for="license_id" class="form-label">資格</label>
                    <select name="license_id" id="license_id" class="form-control">
                        <option value="{{ old('license_id', $ref->license->id)}}" hidden>{{ $ref->license->name}}</option>
                        @foreach ($licenses as $license)
                            <option value="{{ $license->id }}">{{ $license->name }}</option>
                        @endforeach
                    </select>

                    {{-- error --}}
                    @error('license_id')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- category --}}
                <div class="mb-3">
                    @foreach ($categories as $category)
                        <div class="form-check form-check-inline">
                        @if (in_array($category->id, $selected_categories))
                            <input type="checkbox" name="category[]" id="{{ $category->name }}" class="form-check-input" value="{{ $category->id }}" checked>
                        @else
                        <input type="checkbox" name="category[]" id="{{ $category->name }}" class="form-check-input" value="{{ $category->id }}">
                        @endif

                        <label for="{{$category->name}}" class="form-label">{{ $category->name }}</label>
                        </div>
                    @endforeach

                    {{-- error --}}
                    @error('category')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- mrsID --}}
                <div class="mb-3">
                    <label for="mrs_member_id" class="form-label fw-bold">mrsメンバーID</label>
                    <input type="text" name="mrs_member_id" id="mrs_member_id" value="{{ old('mrs_member_id', $ref->mrs_member_id) }}" class="form-control">
                    {{-- error --}}
                    @error('mrs_member_id')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>

                {{-- email --}}
                @if ($ref->user)
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $ref->user->email) }}" class="form-control">
                        {{-- error --}}
                        @error('email')
                            <p class="text-danger small">{{ $message }}</p>
                        @enderror                    
                    </div>
                @endif

                {{-- remarks --}}
                <div class="mb-3">
                    <label for="remarks" class="form-label fw-bold">備考</label>
                    <textarea name="remarks" id="remarks" rows="5" class="form-control">{{ old('remarks', $ref->remarks) }}</textarea>
                    {{-- error --}}
                    @error('remarks')
                        <p class="text-danger small">{{ $message }}</p>
                    @enderror
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary px-5">
                        保存する
                    </button>

                    @if (Auth::user()->role_id === 3)
                        <a href="{{ route('admin.referees.showForChief', ['organization' => optional(Auth::user()->referee)->organization_id]) }}"
                        class="btn btn-outline-secondary px-4">
                            一覧へ戻る
                        </a>
                    @else
                        <a href="{{ route('admin.referees.show') }}"
                        class="btn btn-outline-secondary px-4">
                            一覧へ戻る
                        </a>
                    @endif
                </div>
            </form>
        </div>
        </div>
    </div>
@endsection