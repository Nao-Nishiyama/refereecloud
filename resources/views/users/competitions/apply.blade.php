@extends('layouts.app')

@section('title', '大会申込')
    
@section('content')
    <div class="row gx-5 d-flex justify-content-center">
        <div class="col-8">
            <h2 class="text-dark">{{ $competition->name }} - {{{ $competition->type->name }}}</h2>
            <p class="text-dark ms-5">
                    開催地：{{ $competition->city }}<br>
                    会場：{{ $competition->venue }}<br>
                    募集日程：
                    @php
                        $start = \Carbon\Carbon::parse($competition->start_day);
                        $end   = \Carbon\Carbon::parse($competition->end_day);
                    @endphp

                    {{ $start->format('Y年n月j日') }}（{{ $start->isoFormat('dd') }}）
                    @if (!$start->isSameDay($end))〜
                    {{ $end->format('n月j日') }}（{{ $end->isoFormat('ddd') }}）
                    @endif
                    <br>
                    締切：{{ \Carbon\Carbon::parse($competition->application_deadline)->format('n/j') }} <br>
                    対象：
                    @foreach ($competition->competitionLicense as $competition_license)
                        {{ $competition_license->license->name }},
                    @endforeach
                    @foreach ($competition->competitionCategory as $competition_category)
                        {{ $competition_category->category->name }},
                    @endforeach
                    <br>
                    備考：{{ $competition->organizer_message }}<br>

            </p>
                <hr>
            <h5 class="m-4">参加可能な日程の回答</h5>
            <div class="row justify-content-center">
                <div class="col-10">
                @php
                // 日付単位にグルーピング
                $grouped = collect($eligibleCells)->sortBy(['date','official'])->groupBy('date');
                @endphp

                <div class="accordion" id="acc-eligible-cells">
                @forelse($grouped as $date => $rows)
                    @php
                        $panelId = 'acc-'.\Illuminate\Support\Str::slug($date);
                    @endphp

                    <div class="accordion-item mb-2 shadow-sm">
                    <h2 class="accordion-header" id="h-{{ $panelId }}">
                        <button class="accordion-button collapsed" type="button"
                                data-bs-toggle="collapse" data-bs-target="#c-{{ $panelId }}"
                                aria-expanded="false" aria-controls="c-{{ $panelId }}">
                        <i class="fa-regular fa-calendar me-2"></i>
                        {{ \Carbon\Carbon::parse($date)->format('Y/m/d (D)') }}
                        </button>
                    </h2>

                    <div id="c-{{ $panelId }}" class="accordion-collapse collapse" aria-labelledby="h-{{ $panelId }}"
                        data-bs-parent="#acc-eligible-cells">
                        <div class="accordion-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($rows as $row)
                            @php
                                $nid = $row['nomination_id'] ?? 0;

                                $isApplied   = in_array($nid, $appliedNominationIds, true);
                                $isAppointed = in_array($nid, $appointedNominationIds, true);

                                $licBadges = collect($row['license_names'] ?? [])
                                ->map(fn($n)=>'<span class="badge rounded-pill bg-primary-subtle text-primary me-1 mb-1">Lic '.$n.'</span>')
                                ->implode(' ');

                                $catBadges = collect($row['category_names'] ?? [])
                                ->map(fn($n)=>'<span class="badge rounded-pill bg-success-subtle text-success me-1 mb-1">Cat '.$n.'</span>')
                                ->implode(' ');
                            @endphp

                            <li class="list-group-item d-flex flex-column flex-md-row align-items-md-center gap-2 gap-md-3">
                                <div class="flex-fill" style="width: 50%;">
                                    <div class="fw-semibold">{{ $row['official'] }}</div>
                                        <div class="small mt-1">
                                            {!! $licBadges !!}{!! $catBadges !!}
                                            @if(empty($licBadges) && empty($catBadges))
                                                <span class="text-muted">条件なし</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="text-md-end">
                                        @if ($isClosed)
                                            <span class="text-muted">受付終了</span>
                                        @else
                                            @if ($isApplied)
                                                <span class="badge bg-secondary me-2">申込済</span>
                                                <form method="POST"
                                                        action="{{ route('application.cancel', [$competition->id, $row['nomination_id']]) }}"
                                                        class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('申込を取り消します。よろしいですか？')">取消</button>
                                                </form>
                                            @elseif ($isAppointed)
                                                指名参加
                                            @else
                                                <form method="POST" action="{{ route('application.store', $competition->id) }}" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="nomination_id" value="{{ $row['nomination_id'] }}">
                                                    <button type="submit" class="btn btn-sm btn-primary">申込</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    </div>
                @empty
                    <div class="text-muted">あなたが対象の募集はありません。</div>
                @endforelse
                </div>

                {{-- ついでに締切表示（必要なら） --}}
                @isset($isClosed)
                @if($isClosed)
                    <div class="alert alert-secondary mt-3">この大会の申込は締切済みです。</div>
                @endif
                @endisset

                </div>
            </div>
            <div class="text-center">
                <a href="{{route('competitions.show')}}" class="btn btn-dark px-4">戻る</a>
            </div>
        </div>
    </div>
@endsection