<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kurzy') – Přihlášky na kurzy</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
    <div class="wrap topbar__inner">
        <a class="brand" href="{{ route('courses.index') }}">Přihlášky na kurzy</a>
        <nav class="nav" aria-label="Hlavní navigace">
            <a href="{{ route('courses.index') }}" @class(['is-active' => request()->routeIs('courses.*')])>Kurzy</a>
            <a href="{{ route('participants.index') }}" @class(['is-active' => request()->routeIs('participants.*')])>Účastníci</a>
        </nav>
    </div>
</header>

<main class="wrap">
    @if (session('status'))
        <div class="flash flash--ok" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any() && ! trim($__env->yieldContent('inline-errors')))
        <div class="flash flash--error" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>
