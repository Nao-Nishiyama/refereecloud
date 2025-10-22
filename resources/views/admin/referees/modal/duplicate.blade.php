<div class="modal fade" id="dupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">重複の可能性があります</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">以下の審判員と「漢字/カナ/英字」のいずれかの氏名セットが一致しました。</p>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead>
              <tr><th>氏名（漢字）</th><th>氏名（カナ）</th><th>英字</th><th>資格</th><th>所属</th></tr>
            </thead>
            <tbody id="dupTbody"><tr><td colspan="5" class="text-muted">読み込み中…</td></tr></tbody>
          </table>
        </div>
        <p class="mt-2 text-muted small">※ 重複の疑いがあるため、登録を一旦停止しています。</p>
      </div>
      <div class="modal-footer">
        <a href="{{ route('admin.referees.show') }}" class="btn btn-outline-secondary">キャンセル（一覧へ）</a>
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">戻る（修正する）</button>
      </div>
    </div>
  </div>
</div>
