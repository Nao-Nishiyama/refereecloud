@extends('layouts.app')
@section('title','講習会・案内')

@section('content')
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">講習会・案内</h4>
  </div>

  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <tbody>
        @forelse($trainings as $t)
          <tr>
            <td>{{ $t->title }}</td>
            <td>{{ $t->event_date?->format('Y/n/j') ?? '日程未定' }}</td>
            <td>
              <a href="{{ route('trainings.show', $t) }}" class="btn btn-outline-secondary btn-sm">
                詳細
              </a>
            </td>
            <td class="text-nowrap">
            @php
              $latestFile = $t->files->first();
            @endphp

            @if($latestFile)
              <a href="{{ asset('storage/'.$latestFile->path) }}"
                target="_blank"
                class="btn btn-outline-dark btn-sm">
                資料
              </a>
            @else
              <span class="text-muted small">資料なし</span>
            @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="3" class="text-muted">現在、案内はありません。</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $trainings->links() }}
  </div>
</div>
@endsection
