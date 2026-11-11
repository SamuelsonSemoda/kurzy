@extends('layouts.app')
@section('title', 'Nový účastník')
@section('inline-errors', '1')

@section('content')
    <div class="page-head"><h1>Nový účastník</h1></div>
    <form class="card form" method="POST" action="{{ route('participants.store') }}">
        @csrf
        @include('participants._form')
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Vytvořit účastníka</button>
            <a class="btn btn--ghost" href="{{ route('participants.index') }}">Zpět</a>
        </div>
    </form>
@endsection
