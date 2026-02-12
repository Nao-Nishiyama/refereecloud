@extends('layouts.app')

@section('title', '大会リスト')
    
@section('content')
<div class="container">
    <table class="table table-fixed align-middle table-hover bg-white border text-secondary text-center shadow">
        <thead class="small table-success text-secondary">
            <tr>
                <th>大会名</th>
                <th>種別</th>
                <th>会場</th>
                <th>募集</th>
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
                $comps = $all;
                @endphp
            @endif

            @if($comps)
                @foreach ($comps as $c)
                <tr>
                    <td class="td-ellipsis">
                        <div class="line">{{ $c->name }}</div>
                    </td>
                    <td class="td-ellipsis">
                        <div class="badge bg-success-subtle text-success">
                            {{ \Illuminate\Support\Str::substr($c->type->name, 0, 1) }}
                        </div>
                    </td>
                    <td class="td-ellipsis">
                        <div class="line fw-semibold">{{ $c->venue }}</div>
                        <div class="line small text-muted">{{ $c->city }}</div>
                    </td>
                    <td class="td-ellipsis">
                        @if ($c->start_day === $c->end_day)
                            <span class="line">
                                {{ \Carbon\Carbon::parse($c->start_day)->format('y.n.j') }}
                            </span>
                        @else
                            <span class="line">
                                {{ \Carbon\Carbon::parse($c->start_day)->format('y.n.j') }}
                                –
                                {{ \Carbon\Carbon::parse($c->end_day)->format('n.j') }}
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($c->application_deadline)->format('n.j') }}
                    </td>
                    <td class="text-center">
                    <a href="{{route('admin.competitions.showdetail', $c->id)}}">
                    <i class="fa-solid fa-circle-info text-primary"></i>
                    </a>
                    </td>
                    <td class="text-center">
                    <a href="{{ route('admin.competitions.edit', $c->id) }}">
                    <i class="fa-solid fa-pen-to-square text-danger"></i>
                    </a>
                    </td>
                    <td class="text-center">
                    @if ($c->deleted_at == NULL) 
                    <form method="POST"
                        action="{{ route('admin.competitions.destroy', $c->id) }}"
                        class="d-inline"
                        onsubmit="return confirm('大会「{{ $c->name }}」を削除します。よろしいですか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-link p-0 border-0" title="削除">
                        <i class="fa-solid fa-trash-can text-secondary"></i>
                    </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.competitions.restore', $c->id) }}" class="d-inline">
                    @csrf @method('PATCH')
                    <button class="btn btn-link p-0 text-success">
                        <i class="fa-solid fa-arrows-rotate"></i>
                    </button>
                    </form>                                    
                    @endif
                    </td>
                    @if (Auth::user()->role_id === 1)
                    <td class="text-center">
                    <form method="POST" action="{{ route('admin.competitions.force', $c->id) }}" class="d-inline"
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
                <tr>
                    <td colspan="8">募集なし</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection