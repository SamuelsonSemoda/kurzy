@extends('layouts.app')
@section('title', 'Upravit kurz')
@section('inline-errors', '1')

@section('content')
    <div class="page-head"><h1>Upravit kurz</h1></div>
    <form class="card form" method="POST" action="{{ route('courses.update', $course) }}">
        @csrf
        @method('PUT')
        @include('courses._form')
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Uložit změny</button>
            <a class="btn btn--ghost" href="{{ route('courses.show', $course) }}">Zpět na detail</a>
        </div>
    </form>
@endsection
