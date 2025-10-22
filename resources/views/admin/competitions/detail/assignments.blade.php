@extends('layouts.app')

@section('title', '指名候補')

@section('content')
<div class="container my-4">
  <h5 class="mb-3">
    <form action="post" class="form-control">
      <select name="referee" id="" class="form-select">
        @foreach ($all_referees as $ref)
        <option value="">{{$ref->surname_kanji}} {{$ref->name_kanji}}</option>
        @endforeach
      </select>

      <select name="referee" id="" class="form-select">
        @foreach ($all_referees as $ref)
        <option value="">{{$ref->surname_kanji}} {{$ref->name_kanji}}</option>
        @endforeach
      </select>
    </form>
  </h5>
@endsection