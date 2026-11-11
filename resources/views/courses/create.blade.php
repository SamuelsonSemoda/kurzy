@extends('layouts.app')
@section('title', 'Nový kurz')
@section('inline-errors', '1')

@section('content')
    <div class="page-head"><h1>Nový kurz</h1></div>
    <form class="card form" method="POST" action="{{ route('courses.store') }}">
        @csrf
        @include('courses._form')
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Vytvořit kurz</button>
            <a class="btn btn--ghost" href="{{ route('courses.index') }}">Zpět</a>
        </div>
    </form>
@endsection
