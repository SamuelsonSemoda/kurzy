@extends('layouts.app')
@section('title', 'Upravit účastníka')
@section('inline-errors', '1')

@section('content')
    <div class="page-head"><h1>Upravit účastníka</h1></div>
    <form class="card form" method="POST" action="{{ route('participants.update', $participant) }}">
        @csrf
        @method('PUT')
        @include('participants._form')
        <div class="form-actions">
            <button class="btn btn--primary" type="submit">Uložit změny</button>
            <a class="btn btn--ghost" href="{{ route('participants.index') }}">Zpět</a>
        </div>
    </form>
@endsection
