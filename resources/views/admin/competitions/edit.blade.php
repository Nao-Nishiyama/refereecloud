@extends('layouts.app')

@section('title', '大会編集')
    
@section('content')
    <style>
        /* 縦横スクロール領域 */
        .sticky-table{
        max-height: 70vh;      /* 好きな高さに */
        overflow: auto;        /* 縦横スクロール有効化 */
        }

        /* ヘッダー（最上段）を固定 */
        .sticky-table thead th{
        position: sticky;
        top: 0;
        z-index: 3;            /* 下のセルより手前に */
        background: #f8f9fa;   /* .table-light に合わせる */
        box-shadow: 0 2px 0 rgba(0,0,0,.05); /* 罫線の見切れ対策（任意） */
        }

        /* 最初の列（行見出しや1列目）を固定 */
        .sticky-table tbody th,
        .sticky-table tbody td:first-child{
        position: sticky;
        left: 0;
        z-index: 2;
        background: #fff;      /* 表の地色に合わせる */
        box-shadow: 2px 0 0 rgba(0,0,0,.05); /* 罫線の見切れ対策（任意） */
        }

        /* 左上の角セル（ヘッダーの1列目）は両方固定＆一番手前に */
        .sticky-table thead th:first-child{
        left: 0;
        z-index: 4;
        }
    </style>

    <div class="row gx-5 d-flex justify-content-center">
        <div class="col-8">
            <h5 class="m-4">
                {{__('大会情報及び募集内容の更新')}}
            </h5>

            <div class="ms-4 mt-2 border p-3 shadow-sm rounded">
                <form action="{{ route('admin.competitions.update', $competition->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
    
                {{-- 大会名 --}}
                    <div class="row mb-1">
                        <div class="col-2 text-end">
                            <label for="name" class="form-label">{{('大会名')}}</label>
                        </div>
                        <div class="col-10">
                            <input type="text" name="name" id="name" value="{{ old('name', $competition->name) }}" class="form-control">
                        </div>
                        {{-- error --}}
                        @error('name')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- 種別 --}}
                    <div class="row mb-1">
                        <div class="col-2 text-end">
                            <label for="type_id" class="form-label">{{('種別')}}</label>
                        </div>
                        <div class="col-4">
                            <select name="type_id" id="type_id" class="form-select">
                                @foreach($all_types as $type)
                                    <option value="{{ $type->id }}" 
                                            {{ old('type_id', $competition->type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    {{ $type->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('type_id')<div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- error --}}
                        @error('type')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- 募集日程 --}}
                    <div class="row mb-1">
                        <label class="col-form-label col-2 text-end">{{('募集日程')}}</label>
                        <div class="col-auto">
                            <input type="date" name="start_day" class="form-control text-center" value="{{ old('start_day', $competition->start_day) }}">
                            @error('start_day')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-auto">〜</div>
                        <div class="col-auto">
                            <input type="date" name="end_day" class="form-control text-center" value="{{ old('end_day', $competition->end_day) }}">
                            @error('end_day')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        {{-- error --}}
                        @error('city')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- 開催地 --}}
                    <div class="row mb-1">
                        <div class="col-2 text-end">
                            <label for="city" class="form-label">{{('開催地')}}</label>
                        </div>
                        <div class="col-4">
                            <input type="text" name="city" id="city" value="{{ old('city', $competition->city) }}" class="form-control">
                        </div>
                        {{-- error --}}
                        @error('city')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- 会場 --}}
                    <div class="row mb-1">
                        <div class="col-2 text-end">
                            <label for="venue" class="form-label">{{('会場')}}</label>
                        </div>
                        <div class="col-10">
                            <input type="text" name="venue" id="venue" value="{{ old('venue', $competition->venue) }}" class="form-control">
                        </div>
                        {{-- error --}}
                        @error('venue')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>
                        
                {{-- 申込締め切り --}}
                    <div class="row mb-1">
                        <div class="col-2 text-end">
                            <label for="appication_deadline" class="col-form-label">{{('申込締切')}}</label>
                        </div>
                        <div class="col-auto">
                            <input type="date" name="application_deadline" class="form-control text-center" value="{{ old('application_deadline', $competition->application_deadline) }}">
                        </div>
                        @error('application_deadline')
                            <p class="text-danger small">{{ $message }}</p>
                        @enderror
                        
                        {{-- error --}}
                        @error('venue')
                        <p class="text-danger small">{{ $message }}</p>
                        @enderror
                    </div>

                {{-- Organizer メッセージ --}}
                    <div class="row mb-2">
                        <label class="col-form-label col-4 text-end">{{('備考（ユーザー向け）')}}</label>
                        <div class="col-8">
                            <textarea name="organizer_message" class="form-control" rows="4">{{ old('organizer_message', $competition->organizer_message) }}</textarea>
                            @error('organizer_message')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>

                {{-- 管理者メモ --}}
                    <div class="row mb-2">
                        <label class="col-form-label col-4 text-end">{{('メモ（管理者のみ）')}}</label>
                        <div class="col-8">
                            <textarea name="admin_private_note" class="form-control" rows="4">{{ old('admin_private_note', $competition->admin_private_note) }}</textarea>
                            @error('admin_private_note')<div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                <hr>
                    @php
                    use Carbon\Carbon;

                    /** start_day〜end_day の日付（Carbon配列を想定。無ければ作成）
                    $dates はコントローラで CarbonPeriod から作って渡してある想定 */
                    $datesList = collect($dates)->map(fn($d) => $d instanceof Carbon ? $d : Carbon::parse($d));

                    /** 既存ノミネーション → official|date の事前チェック用セット */
                    $prechecked = $competition->nominations
                        ->mapWithKeys(fn($n) => [
                            $n->official_id.'|'.($n->date instanceof Carbon ? $n->date->toDateString() : (string)$n->date) => true
                        ])->all();

                    /** 列 = 全ライセンス + 全カテゴリー（順番はお好みで） */
                    $columns = collect()
                        ->concat($all_licenses->map(fn($lic) => ['kind'=>'license','id'=>$lic->id,'label'=>$lic->name]))
                        ->concat($all_categories->map(fn($cat) => ['kind'=>'category','id'=>$cat->id,'label'=>$cat->name]));
                    @endphp

                    {{-- ===== グローバル 全選択/全解除 ===== --}}
                    <div class="mb-2 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">全選択（全体）</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">全解除（全体）</button>
                    </div>

                    <div class="table-responsive sticky-table">
                    <table class="table table-bordered align-middle table-sm">
                        <thead class="table-light align-middle">
                        <tr>
                            <th class="text-center" style="min-width:120px;">役職</th>
                            @foreach ($columns as $col)
                            <th class="text-center" style="min-width:120px;">
                                <div>{{ $col['label'] }}</div>
                                <div class="btn-group btn-group-sm mt-1" role="group" aria-label="column toggle">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="toggleByColumn('{{ $col['kind'] }}', {{ $col['id'] }}, true)">全選択</button>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="toggleByColumn('{{ $col['kind'] }}', {{ $col['id'] }}, false)">全解除</button>
                                </div>
                            </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($all_officials as $official)
                            <tr>
                            <th class="bg-light align-top text-center">
                                <div>{{ $official->name }}</div>
                                <div class="btn-group btn-group-sm mt-2" role="group" aria-label="row toggle">
                                <button type="button" class="btn btn-outline-primary"
                                        onclick="toggleByOfficial({{ $official->id }}, true)">全選択</button>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="toggleByOfficial({{ $official->id }}, false)">全解除</button>
                                </div>
                            </th>
                                @foreach ($columns as $col)
                                <td>
                                    @foreach ($datesList as $d)
                                    @php
                                        $dateStr = $d->toDateString();
                                        $id = "cb-{$official->id}-{$col['kind']}-{$col['id']}-".$d->format('Ymd');
                                        $key = $official->id.'|'.$dateStr;

                                        // 該当セルのフィルタ集合（なければ空）
                                        $sel = $cellFilters[$key] ?? ['license_ids'=>[], 'category_ids'=>[]];

                                        // 列ごとのチェック判定
                                        $checked = $col['kind'] === 'license'
                                        ? in_array($col['id'], $sel['license_ids'], true)
                                        : in_array($col['id'], $sel['category_ids'], true);
                                    @endphp

                                    <div class="form-check form-check-inline mb-2 ms-2">
                                        <input type="checkbox"
                                        id="{{ $id }}"
                                        class="form-check-input combo-cb"
                                        data-official-id="{{ $official->id }}"
                                        data-kind="{{ $col['kind'] }}"
                                        data-col-id="{{ $col['id'] }}"
                                        data-date="{{ $dateStr }}"
                                        name="combos[{{ $official->id }}][{{ $col['kind'] }}][{{ $col['id'] }}][]"
                                        value="{{ $dateStr }}"
                                        @checked($checked)
                                        >
                                        <label for="{{ $id }}" class="form-check-label small">{{ $d->format('n/j') }}</label>
                                    </div><br>
                                    @endforeach
                                </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    </div>

                    {{-- ===== 一括トグル JS ===== --}}
                    <script>
                    function setChecked(selector, state) {
                        document.querySelectorAll(selector).forEach(cb => { cb.checked = !!state; });
                    }
                    // 全体
                    function toggleAll(state) {
                        setChecked('.combo-cb', state);
                    }
                    // 役職（行）単位
                    function toggleByOfficial(officialId, state) {
                        setChecked(`.combo-cb[data-official-id="${officialId}"]`, state);
                    }
                    // 資格 or カテゴリー（列）単位
                    function toggleByColumn(kind, colId, state) {
                        setChecked(`.combo-cb[data-kind="${kind}"][data-col-id="${colId}"]`, state);
                    }
                    </script>

                    <div class="text-center">
                        <button type="submit" class="btn btn-warning px-5 mt-2">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection