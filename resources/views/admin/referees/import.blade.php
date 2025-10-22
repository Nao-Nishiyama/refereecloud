<div class="container">
  <h4>レフェリーデータベース CSV インポート</h4>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  @if (session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <form action="{{ route('admin.referees.import.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label class="form-label">CSVファイル</label>
      <input type="file" name="csv" class="form-control" accept=".csv,text/csv">
    </div>

    <div class="mb-3">
      <label class="form-label">文字コード</label>
      <select name="encoding" class="form-select">
        <option value="utf8">UTF-8</option>
        <option value="sjis">Shift_JIS（Excel出力など）</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">アップサートのユニークキー</label>
      <select name="unique_key" class="form-select">
        <option value="registration_number">registration_number</option>
      </select>
      <small class="text-muted">既存データと重複した場合、このキーで更新に切り替えます。</small>
    </div>

    <button class="btn btn-primary">インポート実行</button>
  </form>

  <hr>
  <p class="text-muted">
    * 先頭行はヘッダーとみなします。<br>
    * 日付は <code>1999/1/2</code> などでも取り込み時に <code>1999-01-02</code> へ正規化します。<br>
    * 空文字はNULLに変換します（数値FKや日付など）。
  </p>
</div>