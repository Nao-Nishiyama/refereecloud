@extends('layouts.app')

@section('title', '大会リスト')
    
@section('content')
<div class="container">
    <div class="row gx-5 d-flex justify-content-center table-responsive">
        {{-- Competition List --}}
        <table class="table align-middle table-hover bg-white border text-secondary text-center shadow">
            <thead class="small table-success text-secondary">
                <tr>
                    <th>大会名</th>
                    <th>種別</th>
                    <th>会場</th>
                    <th>募集日程</th>
                    <th>締切</th>
                    <th class="text-center">詳細</th>
                    <th class="text-center">編集</th>
                    <th class="text-center">状態</th>
                    @if (Auth::user()->role_id === 1)
                        <th>削除</th>
                    @endif
                </tr>
            </thead>
            <tbody class="text-start">
                @if (Auth::user()->role_id === 1)
                    @php
                        $competitions = $all;
                    @endphp
                @endif
                @if($competitions)
                    @foreach ($competitions as $competition)
                        <tr>
                            <td>
                                <span class="d-inline-block text-truncate"
                                    title="{{ $competition->name }}">
                                    {{ \Illuminate\Support\Str::limit($competition->name, 6) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">
                                    {{ \Illuminate\Support\Str::limit($competition->type->name, 2) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ \Illuminate\Support\Str::limit($competition->venue, 4) }}</div>
                                <div class="small text-muted">{{ $competition->city }}</div>
                            </td>
                            <td class="text-nowrap">
                                @if ($competition->start_day === $competition->end_day)
                                    {{ \Carbon\Carbon::parse($competition->start_day)->format('y/n/j') }}
                                @else
                                    {{ \Carbon\Carbon::parse($competition->start_day)->format('y/n/j') }}
                                    –
                                    {{ \Illuminate\Support\Str::limit(\Carbon\Carbon::parse($competition->end_day)->format('n/j'), 2) }}
                                @endif
                            </td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}</td>
                            <td class="text-center">
                                <a href="{{route('admin.competitions.showdetail', $competition->id)}}">
                                    <i class="fa-solid fa-circle-info text-primary"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.competitions.edit', $competition->id) }}">
                                    <i class="fa-solid fa-pen-to-square text-danger"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                @if ($competition->deleted_at == NULL) 
                                    <form method="POST"
                                            action="{{ route('admin.competitions.destroy', $competition->id) }}"
                                            class="d-inline"
                                            onsubmit="return confirm('大会「{{ $competition->name }}」を削除します。よろしいですか？');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link p-0 border-0" title="削除">
                                            <i class="fa-solid fa-trash-can text-secondary"></i>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.competitions.restore', $competition->id) }}" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-link p-0 text-success">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                        </button>
                                    </form>                                    
                                @endif
                            </td>
                            @if (Auth::user()->role_id === 1)
                            <td class="text-center">
                                    <form method="POST" action="{{ route('admin.competitions.force', $competition->id) }}" class="d-inline"
                                        onsubmit="return confirm('完全に削除します。よろしいですか？')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link p-0 text-danger">
                                        <i class="fa-solid fa-circle-xmark"></i>
                                    </button>
                                    </form>
                                </td>
                                @endif
                        </tr>                    
                    @endforeach
                @else                
                    <tr><td colspan="8">募集なし</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection