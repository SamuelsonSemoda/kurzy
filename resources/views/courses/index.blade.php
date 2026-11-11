@extends('layouts.app')
@section('title', 'Kurzy')
@section('inline-errors', '1')

@section('content')
    <div class="page-head">
        <h1>Kurzy</h1>
        <a class="btn btn--primary" href="{{ route('courses.create') }}">Nový kurz</a>
    </div>

    <form class="filter" method="GET" action="{{ route('courses.index') }}">
        <div class="field">
            <label for="from">Začíná od</label>
            <input type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}">
        </div>
        <div class="field">
            <label for="to">Začíná do</label>
            <input type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}">
        </div>
        <label class="check">
            <input type="checkbox" name="only_free" value="1" @checked(! empty($filters['only_free']))>
            Jen s volnými místy
        </label>
        <div class="filter__actions">
            <button class="btn" type="submit">Filtrovat</button>
            @if (request()->hasAny(['from', 'to', 'only_free']))
                <a class="btn btn--ghost" href="{{ route('courses.index') }}">Zrušit filtr</a>
            @endif
        </div>
    </form>

    @if ($errors->any())
        <div class="flash flash--error" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    @if ($courses->isEmpty())
        <p class="empty">Žádný kurz neodpovídá zadanému filtru. <a href="{{ route('courses.create') }}">Vytvořte nový kurz.</a></p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Kurz</th>
                    <th>Kód</th>
                    <th>Začátek</th>
                    <th>Obsazenost</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td><a class="strong" href="{{ route('courses.show', $course) }}">{{ $course->name }}</a></td>
                        <td><code>{{ $course->code }}</code></td>
                        <td>{{ $course->starts_at->format('j. n. Y H:i') }}</td>
                        <td>@include('courses._seats', ['course' => $course])</td>
                        <td class="actions">
                            <a href="{{ route('courses.show', $course) }}">Detail</a>
                            <a href="{{ route('courses.edit', $course) }}">Upravit</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination">{{ $courses->links('pagination') }}</div>
    @endif
@endsection
