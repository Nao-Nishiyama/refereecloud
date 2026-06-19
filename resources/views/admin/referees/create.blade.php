@extends('layouts.app')

@section('title', '審判員の新規登録')

@section('content')
<div class="container w-75">
    <h5 class="mb-3">審判員の新規登録</h5>

    <form id="ref-form" action="{{ route('admin.referees.store') }}" method="post" class="border rounded p-3">
        @csrf

        <div class="row g-3">
            <span class="text-danger" style="font-size: 1em">*<span class="text-danger" style="font-size: 0.75em;">必須項目</span></span>
            <div class="col-md-3">
                <label class="form-label">
                    姓（漢字） <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="surname_kanji" value="{{ old('surname_kanji') }}" class="form-control" required>
                @error('surname_kanji')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    名（漢字） <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="name_kanji" value="{{ old('name_kanji') }}" class="form-control" required>
                @error('name_kanji')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    セイ <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="surname_kana" value="{{ old('surname_kana') }}" class="form-control" required>
                @error('surname_kana')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    メイ <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="name_kana" value="{{ old('name_kana') }}" class="form-control" required>
                @error('name_kana')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            
            <div class="col-md-3">
                <label class="form-label">
                    セイ（英字） <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="surname" value="{{ old('surname') }}" class="form-control" required>
                @error('surname')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">
                    メイ（英字） <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">性別</label>

                <select name="gender" class="form-select">
                    <option value="">選択してください</option>
                    <option value="1"
                        @selected(old('gender', $referee->gender ?? '')=='male')>
                        男性
                    </option>
                    <option value="2"
                        @selected(old('gender', $referee->gender ?? '')=='female')>
                        女性
                    </option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">登録番号</label>
                <input type="text" name="registration_number" value="{{ old('registration_number') }}" class="form-control">
                @error('registration_number')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    所属団体 <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <select name="organization_id" class="form-select" required>
                    <option value="">選択してください</option>
                    @foreach($organizations as $o)
                        <option value="{{ $o->id }}" @selected(old('organization_id')==$o->id)>{{ $o->short_name ?? $o->name }}</option>
                    @endforeach
                </select>
                @error('organization_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    資格 <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <select name="license_id" class="form-select" required>
                    <option value="">選択してください</option>
                    @foreach($licenses as $l)
                        <option value="{{ $l->id }}" @selected(old('license_id')==$l->id)>{{ $l->name }}</option>
                    @endforeach
                </select>
                @error('license_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    都道府県 <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <select name="prefecture_id" class="form-select">
                <option value="">未選択</option>
                @foreach($prefectures as $p)
                    <option value="{{ $p->id }}" @selected(old('prefecture_id')==$p->id)>{{ $p->name }}</option>
                @endforeach
                </select>
                @error('prefecture_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    生年月日 <span class="text-danger" style="font-size: 1em">*</span>
                </label>
                <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="form-control">
                @error('birth_date')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label class="form-label">JVA-MRS ID</label>
                <input type="text" name="mrs_member_id" value="{{ old('mrs_member_id') }}" class="form-control">
                @error('mrs_member_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label class="form-label">備考</label>
                <textarea name="remarks" rows="3" class="form-control">{{ old('remarks') }}</textarea>
                @error('remarks')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="text-center mt-3">
            <button type="button" id="btn-open-check" class="btn btn-primary px-5">登録する</button>
            <a href="{{ route('index') }}" class="btn btn-outline-secondary ms-2">戻る</a>
        </div>
        </form>
        
    {{-- ▼ モーダルは別Bladeを読み込み（IDはJSと一致させる） --}}
    @include('admin.referees.modal.duplicate')  {{-- id="dupModal" / tbody#dupTbody --}}
    @include('admin.referees.modal.confirm')    {{-- id="confirmModal" / tbody#confirmTableBody --}}

    @push('scripts')
    <script>
    (function () {
    const form  = document.getElementById('ref-form');
    const btn   = document.getElementById('btn-open-check');
    const btnOk = document.getElementById('btn-confirm-submit');

    const DupModal = () => new window.bootstrap.Modal(document.getElementById('dupModal'));
    const CfModal  = () => new window.bootstrap.Modal(document.getElementById('confirmModal'));

    const serialize = (form) => Object.fromEntries(new FormData(form).entries());

    const fillDupTable = (list) => {
        const tbody = document.getElementById('dupTbody');
        tbody.innerHTML = list.map(c => `
        <tr>
            <td>${c.kanji}</td>
            <td>${c.kana}</td>
            <td>${c.roman}</td>
            <td>${c.license}</td>
            <td>${c.org}</td>
        </tr>`).join('') || '<tr><td colspan="5" class="text-muted">候補なし</td></tr>';
    };

    const pickText = (sel) => document.querySelector(sel+' option:checked')?.textContent?.trim() ?? '';
    const fillConfirmTable = (data) => {
        const rows = [
        ['氏名（漢字）', `${data.surname_kanji ?? ''} ${data.name_kanji ?? ''}`],
        ['氏名（カナ）', `${data.surname_kana ?? ''} ${data.name_kana ?? ''}`],
        ['氏名（英字）', `${data.surname ?? ''} ${data.name ?? ''}`],
        ['登録番号', data.registration_number || '（未入力）'],
        ['所属団体', pickText('select[name="organization_id"]')],
        ['資格',     pickText('select[name="license_id"]')],
        ['都道府県', pickText('select[name="prefecture_id"]') || '未選択'],
        ['生年月日', data.birth_date || ''],
        ['MRS会員番号', data.mrs_member_id || ''],
        ['備考', (data.remarks || '').replace(/\n/g,'<br>')],
        ];
        document.getElementById('confirmTableBody').innerHTML =
        rows.map(([k,v]) => `<tr><th class="text-nowrap" style="width:10rem">${k}</th><td>${v}</td></tr>`).join('');
    };

    btn?.addEventListener('click', async (e) => {
        e.preventDefault();
        const payload = serialize(form);

        try {
        const res = await window.axios.post(
            "{{ route('admin.referees.check-duplicate') }}",
            payload,
            { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } }
        );

        if (res.data?.hasDuplicates) {
            fillDupTable(res.data.candidates || []);
            DupModal().show();
        } else {
            fillConfirmTable(payload);
            CfModal().show();
        }
        } catch (err) {
        console.error(err);
        alert('保存前チェックに失敗しました。入力をご確認ください。');
        }
    });

    document.getElementById('btn-confirm-submit')?.addEventListener('click', (e) => {
        e.preventDefault();
        form.submit(); // 確定して保存
    });
    })();
    </script>
    @endpush

@endsection