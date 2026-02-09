@extends('layouts.app')
@section('title', $training->title)

@section('content')
<div class="container my-4">
  <h3 class="mb-2">{{ $training->title }}</h3>

  <div class="text-muted small mb-3">
    開催日：{{ $training->event_date?->format('Y年n月j日') ?? '未定' }}
    @if($training->venue) ／ 会場：{{ $training->venue }} @endif
    @if($training->deadline) ／ 締切：{{ $training->deadline->format('Y年n月j日') }} @endif
  </div>

  @if($training->summary)
    <div class="border rounded p-3 mb-3 bg-light">{{ $training->summary }}</div>
  @endif

  <div class="lh-lg">
    {!! nl2br(e($training->body ?? '')) !!}
  </div>
@if($training->files->isNotEmpty())
  <ul class="list-group">
    @foreach($training->files as $f)
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <a href="{{ asset('storage/'.$f->path) }}" target="_blank">
          {{ $f->original_name }}
        </a>
        <span class="text-muted small">{{ $f->created_at->format('Y/m/d H:i') }}</span>
      </li>
    @endforeach
  </ul>
@else
  <span class="text-muted small">資料なし</span>
@endif

  <div class="text-center mt-4">
    <a href="{{ route('trainings.index') }}" class="btn btn-dark">一覧へ戻る</a>
  </div>
</div>
@endsection
