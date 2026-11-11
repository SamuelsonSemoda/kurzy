@extends('layouts.app')
@section('title', 'Účastníci')

@section('content')
    <div class="page-head">
        <h1>Účastníci</h1>
        <a class="btn btn--primary" href="{{ route('participants.create') }}">Nový účastník</a>
    </div>

    @if ($participants->isEmpty())
        <p class="empty">Zatím tu nikdo není. <a href="{{ route('participants.create') }}">Přidejte prvního účastníka.</a></p>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>Jméno</th><th>E-mail</th><th>Aktivní přihlášky</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($participants as $participant)
                    <tr>
                        <td class="strong">{{ $participant->name }}</td>
                        <td>{{ $participant->email }}</td>
                        <td>{{ $participant->active_enrollments_count }}</td>
                        <td class="actions">
                            <a href="{{ route('participants.edit', $participant) }}">Upravit</a>
                            <form method="POST" action="{{ route('participants.destroy', $participant) }}"
                                  onsubmit="return confirm('Smazat účastníka včetně jeho přihlášek?')">
                                @csrf
                                @method('DELETE')
                                <button class="link-btn link-btn--danger" type="submit">Smazat</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination">{{ $participants->links('pagination') }}</div>
    @endif
@endsection
