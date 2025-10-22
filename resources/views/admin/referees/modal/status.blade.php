{{-- Admin --}}
<div class="modal fade" id="role-to-admin-{{ $referee->user->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-danger">
            <div class="modal-header border-danger">
                <h3 class="h5 modal-title text-danger">
                    <i class="fa-solid fa-user-slash"></i> 管理者に変更
                </h3>
            </div>
            <div class="modal-body">
                <span class="fw-bold">{{ $referee->surname_kanji }} {{ $referee->name_kanji }} さんを 管理者 に変更しますか？</span>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.status.makeAdmin', $referee->user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal">
                        キャンセル
                    </button>
                    <button type="submit" class="btn btn-danger btn-sm">変更する</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Committee --}}
<div class="modal fade" id="role-to-committee-{{ $referee->user->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-success">
            <div class="modal-header border-success">
                <h3 class="h5 modal-title text-success">
                    <i class="fa-solid fa-user-slash"></i> 三役に変更
                </h3>
            </div>
            <div class="modal-body">
                <span class="fw-bold">{{ $referee->surname_kanji }} {{ $referee->name_kanji }} {{$referee->user->id}}さんを 三役 に変更しますか？</span>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.status.makeCommittee', $referee->user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-dismiss="modal">
                        キャンセル
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">変更する</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Chief --}}
<div class="modal fade" id="role-to-chief-{{ $referee->user->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header border-primary">
                <h3 class="h5 modal-title text-primary">
                    <i class="fa-solid fa-user-slash"></i> 組織加盟団体審判委員長に変更
                </h3>
            </div>
            <div class="modal-body">
                <span class="fw-bold">{{ $referee->surname_kanji }} {{ $referee->name_kanji }} さんを 組織加盟団体審判委員長 に変更しますか？</span>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.status.makeChief', $referee->user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">
                        キャンセル
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">変更する</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- User --}}
<div class="modal fade" id="role-to-user-{{ $referee->user->id }}">
    <div class="modal-dialog">
        <div class="modal-content border-secondary">
            <div class="modal-header border-secondary">
                <h3 class="h5 modal-title text-secondary">
                    <i class="fa-solid fa-user-slash"></i> 一般ユーザーに変更
                </h3>
            </div>
            <div class="modal-body">
                <span class="fw-bold">{{ $referee->surname_kanji }} {{ $referee->name_kanji }} さんを 一般ユーザー に変更しますか？</span>
            </div>
            <div class="modal-footer border-0">
                <form action="{{ route('admin.status.makeUser', $referee->user->id) }}" method="post">
                    @csrf
                    @method('PATCH')
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        キャンセル
                    </button>
                    <button type="submit" class="btn btn-secondary btn-sm">変更する</button>
                </form>
            </div>
        </div>
    </div>
</div>