@extends('layouts.app')

@section('title','講習会・案内')

@section('content')
@php
  use Illuminate\Support\Str;
@endphp

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">講習会のご案内</h4>

    @canany(['admin','committee'])
      <a href="{{ route('admin.trainings.create') }}" class="btn btn-primary btn-sm">
        新規作成
      </a>
    @endcanany
  </div>

  @if(session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
  @endif

  @forelse($trainings as $t)
    @php
      $latestFile = $t->files->first();
      $isNew = $t->created_at?->gt(now()->subMonths(1));
      $deadlineSoon = $t->deadline ? \Carbon\Carbon::parse($t->deadline)->endOfDay()->diffInDays(now(), false) >= -3 && \Carbon\Carbon::parse($t->deadline)->endOfDay()->isFuture() : false;
    @endphp

    <div class="card shadow-sm mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-2">
          <div class="me-2">
            <h5 class="card-title mb-1">
              <a href="{{ route('trainings.show', $t->id) }}" class="text-decoration-none">
                {{ $t->title }}
              </a>
              @if (!$t->is_published)
                  <span class="fs-6 small text-danger">（未公開）</span>
              @endif
            </h5>
            <div class="text-muted small">
              @if($t->event_date)
                <span class="me-2">
                  <i class="fa-regular fa-calendar"></i>
                  {{ \Carbon\Carbon::parse($t->event_date)->format('Y/n/j') }}
                </span>
              @endif

              @if($t->prefecture)
                <span class="me-2">
                  <i class="fa-solid fa-location-dot"></i>
                  {{ $t->prefecture->name }}
                </span>
              @endif

              @if($t->venue)
                <span class="me-2">会場：{{ $t->venue }}</span>
              @endif

              <span class="ms-2">
                投稿日：{{ $t->created_at?->format('Y/n/j') }}
              </span>
            </div>
          </div>

          <div class="text-end">
            @if($isNew)
              <span class="badge bg-success">新着</span>
            @endif
            @if($deadlineSoon)
              <span class="badge bg-warning text-dark">締切間近</span>
            @endif
            @if($latestFile)
              <span class="badge bg-dark">資料あり</span>
            @endif
          </div>
        </div>

        @if($t->summary)
          <p class="mt-2 mb-2">
            {{ Str::limit($t->summary, 120) }}
          </p>
        @elseif($t->body)
          <p class="mt-2 mb-2 text-muted">
            {{ Str::limit(strip_tags($t->body), 120) }}
          </p>
        @endif

        @if (!$t->trashed())
          <div class="d-flex gap-2 flex-wrap">
            @if ($t->is_published)
              <a href="{{ route('trainings.show', $t->id) }}" class="btn btn-outline-secondary btn-sm">
                詳細
              </a>
            @endif

            @if($t->link_url)
              <a href="{{ $t->link_url }}" target="_blank" rel="noopener"
                class="btn btn-outline-primary btn-sm">
                {{ $t->link_label ?: '関連リンク' }}
              </a>
            @endif

            @if($latestFile)
              <a href="{{ \Illuminate\Support\Facades\Storage::url($latestFile->path) }}"
                target="_blank" class="btn btn-outline-dark btn-sm">
                資料
              </a>
            @endif

            @canany(['admin','committee'])
              <a href="{{ route('admin.trainings.edit', $t->id) }}" class="btn btn-outline-success btn-sm">
                編集
              </a>
            @endcanany
          </div>

          @if($t->deadline)
            <div class="small text-muted mt-2">
              締切：{{ \Carbon\Carbon::parse($t->deadline)->format('Y/n/j') }}
            </div>
          @endif
        @else
            <span class="small test-muted">削除済み</span>
        @endif
      </div>
    </div>

  @empty
    <div class="text-muted">お知らせはありません。</div>
  @endforelse
</div>
@endsection
