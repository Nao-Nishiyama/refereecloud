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
                    <th>開催地</th>
                    <th>会場</th>
                    <th>募集日程</th>
                    <th>締切</th>
                    <th>レフェリー</th>
                    <th>編集</th>
                    <th>状態</th>
                    @if (Auth::user()->role_id === 1)
                        <th>完全消去</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @if (Auth::user()->role_id === 1)
                    @php
                        $competitions = $all;
                    @endphp
                @endif
                @if($competitions)
                    @foreach ($competitions as $competition)
                        <tr>
                            <td>{{ $competition->name }}</td>
                            <td>{{ $competition->type->name }}</td>
                            <td>{{ $competition->city }}</td>
                            <td>{{ $competition->venue }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
                                〜
                                {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}</td>
                            <td>
                                <a href="{{route('admin.competitions.showdetail', $competition->id)}}">
                                    <i class="fa-solid fa-circle-info text-primary"></i>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.competitions.edit', $competition->id) }}">
                                    <i class="fa-solid fa-pen-to-square text-danger"></i>
                                </a>
                            </td>
                            <td>
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
                            <td>
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