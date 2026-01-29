@extends('layouts.app')
@section('title', '審判員CSVエクスポート')

@section('content')
<div class="container">
  <h3 class="mb-3">審判員CSVエクスポート</h3>

  <form method="POST" action="{{ route('admin.referees.export') }}">
    @csrf

    <div class="row g-3">

      {{-- 資格（license）：1カード --}}
      <div class="col-12 col-lg-4">
        <div class="card p-3 h-100">
          <div class="fw-bold mb-2">資格（複数選択）</div>
          @foreach($licenses as $l)
            <label class="d-block">
              <input type="checkbox" name="license_ids[]" value="{{ $l->id }}"
                @checked(in_array($l->id, $licenseIds))>
              {{ $l->name }}
            </label>
          @endforeach
        </div>
      </div>

      {{-- 団体：7つずつ列 --}}
      <div class="col-12 col-lg-8">
        <div class="card p-3 h-100">
          <div class="fw-bold mb-2">団体（複数選択）</div>
          <div class="row">
            @foreach($organizations->chunk(7) as $chunk)
              <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                @foreach($chunk as $o)
                  <label class="d-block">
                    <input type="checkbox" name="organization_ids[]" value="{{ $o->id }}"
                      @checked(in_array($o->id, $organizationIds))>
                    {{ $o->short_name }}
                  </label>
                @endforeach
              </div>
            @endforeach
          </div>
        </div>
      </div>

    </div>

    <div class="mt-4 text-end">
      <button class="btn btn-primary"
        onclick="return confirm('指定条件に一致する審判員をCSVでダウンロードしますか？')">
        CSVダウンロード
      </button>
    </div>
  </form>
</div>
@endsection