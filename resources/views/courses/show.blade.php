@extends('layouts.app')
@section('title', $course->name)
@section('inline-errors', '1')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $course->name }}</h1>
            <p class="muted"><code>{{ $course->code }}</code> · začíná {{ $course->starts_at->format('j. n. Y \v H:i') }}</p>
        </div>
        <div class="head-actions">
            <a class="btn" href="{{ route('courses.edit', $course) }}">Upravit</a>
            <form method="POST" action="{{ route('courses.destroy', $course) }}"
                  onsubmit="return confirm('Opravdu smazat kurz včetně všech přihlášek?')">
                @csrf
                @method('DELETE')
                <button class="btn btn--danger" type="submit">Smazat</button>
            </form>
        </div>
    </div>

    @if ($errors->any())
        <div class="flash flash--error" role="alert">
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="card">
        <h2>Obsazenost</h2>
        @include('courses._seats', ['course' => $course])
    </section>

    <section class="card">
        <h2>Přihlásit účastníka</h2>
        @if ($course->isFull())
            <p class="muted">Kapacita kurzu je vyčerpána. Nové přihlášení bude možné po uvolnění místa.</p>
        @elseif ($availableParticipants->isEmpty())
            <p class="muted">Není koho přihlásit. <a href="{{ route('participants.create') }}">Přidejte účastníka.</a></p>
        @else
            <form class="inline-form" method="POST" action="{{ route('enrollments.store', $course) }}">
                @csrf
                <div class="field">
                    <label for="participant_id">Účastník</label>
                    <select id="participant_id" name="participant_id" required>
                        <option value="">– vyberte –</option>
                        @foreach ($availableParticipants as $p)
                            <option value="{{ $p->id }}" @selected(old('participant_id') == $p->id)>{{ $p->name }} ({{ $p->email }})</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn--primary" type="submit">Přihlásit</button>
            </form>
        @endif
    </section>

    <section>
        <h2>Přihlášení ({{ $enrollments->count() }})</h2>
        @if ($enrollments->isEmpty())
            <p class="empty">Do kurzu se zatím nikdo nepřihlásil.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr><th>Účastník</th><th>E-mail</th><th>Přihlášen</th><th>Stav</th><th></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($enrollments as $enrollment)
                        <tr @class(['is-cancelled' => $enrollment->status->value === 'cancelled'])>
                            <td class="strong">{{ $enrollment->participant->name }}</td>
                            <td>{{ $enrollment->participant->email }}</td>
                            <td>{{ $enrollment->enrolled_at->format('j. n. Y H:i') }}</td>
                            <td><span class="tag tag--{{ $enrollment->status->value }}">{{ $enrollment->status->label() }}</span></td>
                            <td class="actions">
                                @foreach ($enrollment->status->allowedTransitions() as $target)
                                    <form method="POST" action="{{ route('enrollments.status', $enrollment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $target->value }}">
                                        <button class="link-btn" type="submit">
                                            {{ match ($target->value) {
                                                'cancelled' => 'Zrušit',
                                                'completed' => 'Dokončit',
                                                'enrolled' => 'Obnovit',
                                            } }}
                                        </button>
                                    </form>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
