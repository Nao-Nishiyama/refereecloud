@extends('layouts.app')

@section('title', 'Admin_Competition_Control')

@section('content')
@php
  $user       = Auth::user();
  $isAdmin    = ($user->role_id === 1);
  $isCommittee= ($user->role_id === 2);
  $myOrgId    = (int) optional($user->referee)->organization_id;

  // 日付でグループ化
  $grouped = $nominations->groupBy(fn($n) => optional($n->day?->date)->toDateString());

  // 表示対象団体：admin/committee は全団体、chief は自団体のみ
  $orgsForView = ($isAdmin || $isCommittee)
      ? $organizations
      : $organizations->where('id', $myOrgId);
@endphp

<div class="row gx-5 d-flex justify-content-center">
  <div class="col-md-8">
    <h2 class="text-dark">
      {{ $competition->name }} - {{ optional($competition->type)->name }}
    </h2>

    <p class="text-dark ms-5">
      開催地：{{ $competition->city }}<br>
      会場：{{ $competition->venue }}<br>
      募集日程：{{ \Carbon\Carbon::parse($competition->start_day)->format('n/j') }}
      〜 {{ \Carbon\Carbon::parse($competition->end_day)->format('n/j') }}<br>
      締切：{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }}
    </p>
    <hr>

    @if (session('status'))
      <div class="alert alert-info">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.competitions.assign', $competition->id) }}">
      @csrf
      @method('PATCH')

      <table class="table align-middle">
        <thead>
          <tr>
            <th style="width:20%">日付</th>
            <th style="width:30%">役職</th>
            <th style="width:50%">派遣審判員</th>
          </tr>
        </thead>

        <tbody>
        @forelse($grouped as $dateKey => $rows)
          @php
            $rowspan   = $rows->count();
            $dateLabel = $dateKey ? \Carbon\Carbon::parse($dateKey)->format('Y/m/d') : '-';
          @endphp

          @foreach($rows as $idx => $n)
            <tr>
              @if($idx === 0)
                <td rowspan="{{ $rowspan }}" class="align-middle fw-semibold">
                  {{ $dateLabel }}
                </td>
              @endif

              <td class="align-middle">
                {{ $n->official?->name ?? '-' }}

                @php
                  $need = (int)($needTotalByNomination[$n->id] ?? 0);
                  $got  = (int)($assignedTotalByNomination[$n->id] ?? 0);
                @endphp

                <div class="small text-muted mt-1">
                  @if ($isAdmin||$isCommittee)
                    @if ($got<$need)
                    <span class="small text-danger mt-1">
                    @endif
                    集計 {{ $got }}</span> / 
                  @endif
                  必要数 {{ $need }}
                </div>
              </td>


              <td class="align-middle">
                {{-- 全団体ぶん、枠数のある分だけ select を連続で表示（まとまり無し） --}}
                @foreach($orgsForView as $org)
                  @php
                    $orgId = (int) $org->id;

                    // nomination × org の枠数
                    $slots = (int) ($capMatrix[$n->id][$orgId] ?? 0);
                    if ($slots <= 0) {
                      continue; // 枠が0なら表示しない（必要なら表示したい場合はcontinueを外す）
                    }

                    // 事前割当（slot順）
                    $rawPre = $preAssignedMatrix[$n->id][$orgId] ?? [];
                    $pre = array_slice($rawPre, 0, $slots);
                    $pre = array_pad($pre, $slots, null);

                    // 候補者（nomination × org）
                    $cands = $candidatesByNominationOrg[$n->id][$orgId] ?? collect();

                    // 編集可否：adminは全部OK、committeeは自団体のみ、chiefは自団体のみ
                    $editable = $isAdmin || ($orgId === $myOrgId);
                    if ($isCommittee && $orgId !== $myOrgId) $editable = false;
                  @endphp

                  @for($i=0; $i<$slots; $i++)
                    <div class="d-inline-block me-2 mb-2" style="min-width:240px;">
                      <select
                        name="assignments[{{ $n->id }}][{{ $orgId }}][]"
                        class="form-select"
                        @disabled(!$editable)
                      >
                        <option value="" @selected(empty($pre[$i]))>{{ $org->short_name }}｜* *</option>

                        @foreach($cands as $r)
                          <option value="{{ $r->id }}" @selected((int)$pre[$i] === (int)$r->id)>
                            {{ $org->short_name }}｜{{ $r->surname_kanji }} {{ $r->name_kanji }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  @endfor
                @endforeach
                {{-- 枠が一つもない場合 --}}
                @php
                  $hasAnySlot = false;
                  foreach ($orgsForView as $org) {
                    $oid = (int)$org->id;
                    if ((int)($capMatrix[$n->id][$oid] ?? 0) > 0) { $hasAnySlot = true; break; }
                  }
                @endphp
                @if(!$hasAnySlot)
                  <span class="text-muted small">（割当なし）</span>
                @endif
              </td>
            </tr>
          @endforeach

        @empty
          <tr>
            <td colspan="3" class="text-muted">募集セル（nomination）がありません。</td>
          </tr>
        @endforelse
        </tbody>
      </table>

      <div class="text-center">
        <button type="submit" class="btn btn-primary px-4">保存</button>
        <a href="{{ route('admin.competitions.show') }}" class="btn btn-dark px-4">戻る</a>
      </div>
    </form>
  </div>
</div>
@endsection
