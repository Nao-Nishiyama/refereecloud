<div class="container">
    <h4>レフェリーデータベース CSV インポート</h4>

    <div class="mb-3">
        <a href="{{ route('admin.referees.import.template') }}"
           class="btn btn-outline-success btn-sm">
            <i class="fa-solid fa-download"></i>
            CSVテンプレートをダウンロード
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('admin.referees.import.store') }}"
          method="POST"
          enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">CSVファイル</label>
            <input
                type="file"
                name="csv"
                class="form-control"
                accept=".csv,text/csv">
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
                <option value="registration_number" selected>
                    registration_number（登録番号）
                </option>
            </select>

            <small class="text-muted">
                既存データと重複した場合、このキーで更新に切り替えます。
            </small>
        </div>

        <button type="submit" class="btn btn-primary">
            インポート実行
        </button>
    </form>

    <hr>

    <h6 class="text-muted">CSV取込時の注意事項</h6>

    <ul class="text-muted small">
        <li>先頭行はヘッダー行として扱います。</li>
        <li>CSVはUTF-8またはShift_JISに対応しています。</li>
        <li>日付は <code>1999/1/2</code> や <code>1999-01-02</code> の形式で入力できます。</li>
        <li>空文字は <code>NULL</code> として取り込みます。</li>
        <li><code>gender</code> は <strong>1=男性、2=女性</strong> を入力してください。</li>
        <li><code>license_acquired_year</code> は現在資格の取得年として資格履歴に登録されます。（例：2024）</li>
        <li><code>prefecture_id</code>、<code>organization_id</code>、<code>license_id</code> はデータベース上のIDを入力してください。</li>
        <li>CSVテンプレートを利用すると、必要な列名をそのまま使用できます。</li>
    </ul>
</div>