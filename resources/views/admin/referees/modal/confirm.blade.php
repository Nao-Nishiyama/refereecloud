<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">入力内容の確認</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>
        <div class="modal-body">
            <div class="table-responsive">
            <table class="table table-sm">
                <tbody id="confirmTableBody">
                <tr><td class="text-muted">準備中…</td></tr>
                </tbody>
            </table>
            </div>
            <p class="text-muted small mb-0">この内容で保存します。よろしければ「確定して保存」を押してください。</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline-secondary" data-bs-dismiss="modal">戻る</button>
            <button id="btn-confirm-submit" class="btn btn-primary">確定して保存</button>
        </div>
        </div>
    </div>
</div>
