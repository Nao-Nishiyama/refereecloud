@extends('layouts.app')

@section('title', '講習会案内 編集')

@section('content')
<div class="container">
  <div class="row justify-content-center">
    <div class="col-lg-9">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">講習会案内 編集</h4>
        <a href="{{ route('admin.trainings.index') }}" class="btn btn-outline-dark btn-sm">一覧へ</a>
      </div>

      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="fw-semibold mb-1">入力内容にエラーがあります。</div>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @if (session('status'))
        <div class="alert alert-info">{{ session('status') }}</div>
      @endif

      <form method="POST"
            action="{{ route('admin.trainings.update', $training->id) }}"
            enctype="multipart/form-data"
            class="card shadow-sm">
        @csrf
        @method('PATCH')

        <div class="card-body">

          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">タイトル <span class="text-danger">*</span></label>
              <input type="text"
                     name="title"
                     class="form-control"
                     value="{{ old('title', $training->title) }}"
                     required>
            </div>

            <div class="col-md-4">
              <label class="form-label">開催日（任意）</label>
              <input type="date"
                     name="event_date"
                     class="form-control"
                     value="{{ old('event_date', optional($training->event_date)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">締切（任意）</label>
              <input type="date"
                     name="deadline"
                     class="form-control"
                     value="{{ old('deadline', optional($training->deadline)->format('Y-m-d')) }}">
            </div>

            <div class="col-md-4">
              <label class="form-label">公開状態</label>
              <select name="is_published" class="form-select">
                <option value="1" @selected(old('is_published', (string)$training->is_published) == '1')>公開</option>
                <option value="0" @selected(old('is_published', (string)$training->is_published) == '0')>非公開</option>
              </select>
              <div class="form-text">ユーザー一覧に表示するかどうか。</div>
            </div>

            <div class="col-12">
              <label class="form-label">会場（任意）</label>
              <input type="text"
                     name="venue"
                     class="form-control"
                     value="{{ old('venue', $training->venue) }}"
                     placeholder="例：〇〇体育館 / 〇〇会館">
            </div>

            <div class="col-md-6">
              <label class="form-label">都道府県（任意）</label>
              <select name="prefecture_id" class="form-select">
                <option value="">指定なし</option>
                @foreach($prefectures as $pref)
                  <option value="{{ $pref->id }}"
                    @selected((string)old('prefecture_id', $training->prefecture_id) === (string)$pref->id)>
                    {{ $pref->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">団体（任意）</label>
              <select name="organization_id" class="form-select">
                <option value="">指定なし</option>
                @foreach($organizations as $org)
                  <option value="{{ $org->id }}"
                    @selected((string)old('organization_id', $training->organization_id) === (string)$org->id)>
                    {{ $org->short_name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">概要（任意）</label>
              <textarea name="summary"
                        rows="2"
                        class="form-control"
                        placeholder="一覧などで短く表示する用">{{ old('summary', $training->summary) }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label">本文（任意）</label>
              <textarea name="body"
                        rows="8"
                        class="form-control"
                        placeholder="詳細案内本文（持ち物、申込方法、注意事項など）">{{ old('body', $training->body) }}</textarea>
            </div>

            <div class="row g-2">
              <div class="col-12 col-md-8">
                <label class="form-label">関連リンクURL（任意）</label>
                <input type="url" name="link_url" class="form-control"
                      value="{{ old('link_url', $training->link_url ?? '') }}"
                      placeholder="https://...">
                @error('link_url')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>

              <div class="col-12 col-md-4">
                <label class="form-label">リンク表示名（任意）</label>
                <input type="text" name="link_label" class="form-control"
                      value="{{ old('link_label', $training->link_label ?? '') }}"
                      placeholder="例：申込フォーム">
                @error('link_label')<div class="text-danger small">{{ $message }}</div>@enderror
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">資料PDF（差し替え）</label>
              <input type="file"
                     name="pdf"
                     class="form-control"
                     accept="application/pdf">
              <div class="form-text">新しいPDFを選ぶと差し替え（履歴には残します）。</div>

              @if($training->pdf_path)
                <div class="mt-2 d-flex flex-wrap gap-2 align-items-center">
                  <span class="badge text-bg-success">資料あり</span>
                  <a class="btn btn-outline-dark btn-sm"
                     href="{{ asset('storage/'.$training->pdf_path) }}"
                     target="_blank">現在のPDFを開く</a>
                </div>
              @endif
            </div>

          </div>

          {{-- PDF更新履歴 --}}
          @if(!empty($training->files) && $training->files->isNotEmpty())
            <hr class="my-4">
            <h6 class="fw-semibold mb-2">PDF更新履歴</h6>
            <div class="list-group">
              @foreach($training->files as $f)
                <div class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="me-2">
                    <div class="fw-semibold">
                      {{ $f->original_name ?? basename($f->path) }}
                      @if($training->pdf_path === $f->path)
                        <span class="badge text-bg-primary ms-2">最新</span>
                      @endif
                    </div>
                    <div class="small text-muted">
                      {{ optional($f->created_at)->format('Y/n/j H:i') }}
                      @if($f->uploader)
                        ／ 更新者：{{ $f->uploader->referee->surname_kanji ?? ('user#'.$f->uploaded_by) }}
                      @endif
                    </div>
                  </div>
                  <a class="btn btn-outline-secondary btn-sm text-nowrap"
                     href="{{ asset('storage/'.$f->path) }}"
                     target="_blank">開く</a>
                </div>
              @endforeach
            </div>
          @endif

        </div>

        <div class="card-footer bg-white d-flex gap-2 justify-content-center">
          <button type="submit" class="btn btn-success px-4">保存</button>
          <a href="{{ route('trainings.index') }}" class="btn btn-outline-dark px-4">戻る</a>
        </div>
      </form>

      {{-- 削除ボタン（必要なら） --}}
      
      <form method="POST"
            action="{{ route('admin.trainings.destroy', $training->id) }}"
            class="text-center mt-3"
            onsubmit="return confirm('この案内を削除します。よろしいですか？');">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger btn-sm">削除</button>
      </form>
     

    </div>
  </div>
</div>
@endsection
