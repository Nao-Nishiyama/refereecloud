@extends('layouts.app')
@section('title','講習会案内（管理）')

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">講習会案内（管理）</h4>
    <a href="{{ route('admin.trainings.create') }}" class="btn btn-primary btn-sm">新規作成</a>
  </div>

  @if (session('status'))
    <div class="alert alert-info">{{ session('status') }}</div>
  @endif

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <tbody>
      @if (Auth::user()->role_id === 1)
          @php
              $trainings = $trainingsWithTrashed;
          @endphp          
      @endif
      @forelse($trainings as $t)
        <tr>
          <td>{{ $t->title }}</td>
          <td>{{ $t->event_date?->format('Y/n/j') ?? '-' }}</td>
          <td>{{ $t->is_published ? '公開' : '非公開' }}</td>
          <td class="text-center">

          @if ($t->trashed())
            <span class="small text-muted">
              削除済
            </span>
            @else
            <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.trainings.edit', $t) }}">編集</a>
            <form method="POST" action="{{ route('admin.trainings.destroy', $t) }}" class="d-inline"
                    onsubmit="return confirm('削除しますか？')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">削除</button>
              </form>
          </td>

          <td class="text-end">
            @php
              $latestFile = $t->files->first();
            @endphp

            @if($latestFile)
              <a href="{{ asset('storage/'.$latestFile->path) }}"
                target="_blank"
                class="btn btn-outline-dark btn-sm ms-2">
                資料
              </a>
            @else
              <span class="text-muted small">資料なし</span>
            @endif
          @endif
          </td>

        </tr>
      @empty
        <tr><td colspan="4" class="text-muted">まだ作成されていません。</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
