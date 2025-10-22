@extends('layouts.app')

@section('title','重複疑いの連絡')

@section('content')
<div class="container w-75">
  <h5 class="mb-3">重複疑いの連絡</h5>
  <form method="post" action="{{ route('admin.referees.duplicate.report.store') }}" class="border rounded p-3">
    @csrf
    <div class="mb-3">
      <label class="form-label">状況説明</label>
      <textarea name="message" rows="5" class="form-control" required>{{ old('message') }}</textarea>
      @error('message')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    {{-- 元入力を hidden で同梱（管理者が確認しやすい） --}}
    @foreach(($prefill ?? []) as $k => $v)
      <input type="hidden" name="input[{{ $k }}]" value="{{ is_scalar($v)? $v : json_encode($v,JSON_UNESCAPED_UNICODE) }}">
    @endforeach

    <div class="text-center">
      <button class="btn btn-primary px-4">送信</button>
      <a href="{{ route('admin.referees.create') }}" class="btn btn-outline-secondary ms-2">戻る</a>
    </div>
  </form>
</div>
@endsection
