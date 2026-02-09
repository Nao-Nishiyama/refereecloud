@extends('layouts.app')

@section('title', $training->title)

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h4 class="mb-1">{{ $training->title }}</h4>
      <div class="text-muted small">
        投稿日：{{ $training->created_at?->format('Y/n/j H:i') }}
        @if($training->event_date)
          ｜開催日：{{ \Carbon\Carbon::parse($training->event_date)->format('Y/n/j') }}
        @endif
        @if($training->deadline)
          ｜締切：{{ \Carbon\Carbon::parse($training->deadline)->format('Y/n/j') }}
        @endif
      </div>
    </div>

    @canany(['admin','committee'])
      <a href="{{ route('admin.trainings.edit', $training->id) }}" class="btn btn-outline-success btn-sm">編集</a>
    @endcanany
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 small text-muted mb-2">
        @if($training->prefecture)<div class="col-12 col-md-4">都道府県：{{ $training->prefecture->name }}</div>@endif
        @if($training->organization)<div class="col-12 col-md-4">団体：{{ $training->organization->short_name }}</div>@endif
        @if($training->venue)<div class="col-12 col-md-4">会場：{{ $training->venue }}</div>@endif
      </div>

      @if($training->summary)
        <div class="mb-3">{{ $training->summary }}</div>
      @endif

      @if($training->body)
        <div class="mb-3">{!! nl2br(e($training->body)) !!}</div>
      @endif

      <div class="d-flex gap-2 flex-wrap">
        @if($training->link_url)
          <a href="{{ $training->link_url }}" target="_blank" rel="noopener"
             class="btn btn-outline-primary btn-sm">
            {{ $training->link_label ?: '関連リンク' }}
          </a>
        @endif
      </div>
    </div>
  </div>

  <h6 class="mb-2">資料（更新履歴）</h6>
  @if($training->files->isNotEmpty())
    <div class="list-group mb-3">
      @foreach($training->files as $f)
        <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
           href="{{ \Illuminate\Support\Facades\Storage::url($f->path) }}" target="_blank">
          <div class="me-2">
            <div class="fw-semibold">{{ $f->original_name ?? basename($f->path) }}</div>
            <div class="small text-muted">
              {{ $f->created_at?->format('Y/n/j H:i') }}
              @if(isset($f->uploader))｜{{ $f->uploader->referee->surname_kanji }}@endif
            </div>
          </div>
          <span class="badge bg-dark">PDF</span>
        </a>
      @endforeach
    </div>
  @else
    <div class="text-muted small mb-3">資料はありません。</div>
  @endif

  <a href="{{ route('trainings.index') }}" class="btn btn-dark btn-sm">一覧へ戻る</a>
</div>
@endsection
