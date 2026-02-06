@extends('layouts.app')

@section('title', 'Annual Report')
    
@section('content')
    <form action="{{ route('reports.store') }}" method="post" enctype="multipart/form-data">
    @csrf

        <div class="row g-3 justify-content-center mb-1 mt-3">
            <div class="col-2 text-end">
                <label for="year" class="form-label">{{ __('報告年度') }} <span style="font-size: .75em">{{ __('（西暦）')}}</span></label>
            </div>
            <div class="col-2">
                <input type="text" name="year" id="year" class="form-control text-center" value="">
                @error('year')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 主審ブロック以上 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="first_ref_block" class="form-label">{{ __('主審') }} <span style="font-size: .75em">{{ __('（ブロック以上）')}}</span></label>
            </div>
            <div class="col-2">
                <input type="number" name="first_ref_block" id="first_ref_block" class="form-control text-center w-75" value="">
                @error('first_ref_block')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 副審ブロック以上 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="second_ref_block" class="form-label">{{ __('副審') }} <span style="font-size: .75em">{{ __('（ブロック以上）')}}</span></label>
            </div>
            <div class="col-2">
                <input type="number" name="second_ref_block" id="second_ref_block" class="form-control text-center w-75" value="">
                        @error('second_ref_block')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
            </div>
        </div>

        {{-- 主審都道府県 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="first_ref" class="form-label">{{ __('主審') }} <span style="font-size: .75em">{{ __('（都道府県）')}}</span></label>
            </div>
            <div class="col-2">
                <input type="number" name="first_ref" id="first_ref" class="form-control text-center w-75" value="">
                @error('first_ref')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 副審都道府県 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="second_ref" class="form-label">{{ __('副審') }} <span style="font-size: .75em">{{ __('（都道府県）')}}</span></label>
            </div>
            <div class="col-2">
                <input type="number" name="second_ref" id="second_ref" class="form-control text-center w-75" value="">
                @error('second_ref')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 記録 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="scorer" class="form-label">{{ __('記録') }}</label>
            </div>
            <div class="col-2">
                <input type="number" name="scorer" id="scorer" class="form-control text-center w-75" value="">
                @error('scorer')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- AS --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="assistant_scorer" class="form-label">{{ __('AS') }}</label>
            </div>
            <div class="col-2">
                <input type="number" name="assistant_scorer" id="assistant_scorer" class="form-control text-center w-75" value="">
                @error('assistant_scorer')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 線審 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="linejudge" class="form-label">{{ __('線審') }}</label>
            </div>
            <div class="col-2">
                <input type="number" name="linejudge" id="linejudge" class="form-control text-center w-75" value="">
                @error('linejudge')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 講習会 --}}
        <div class="row g-3 justify-content-center mb-1">
            <div class="col-2 text-end">
                <label for="training" class="form-label">{{ __('講習会') }}</label>
            </div>
            <div class="col-2">
                <input type="number" name="training" id="training" class="form-control text-center w-75" value="">
                @error('training')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row justify-content-center">
        <div class="text-center">
            <button type="submit" class="btn btn-outline-success text-center mt-3" style="width: 20%">保存</button>
            <a href="{{route('reports.show', Auth::user()->id )}}" class="btn btn-dark px-4 mt-3 ms-2" style="width: 20%">戻る</a>
        </div>
        </div>
    </form>
@endsection