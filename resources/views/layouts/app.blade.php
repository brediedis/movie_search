<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title') @yield('title') · @endif Filmu meklētājs</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('search') }}" class="brand">
                <span class="brand__mark" aria-hidden="true">▶</span>
                Filmu meklētājs
            </a>
            <span class="site-header__note">Dati no OMDb API</span>
        </div>
    </header>

    <div class="container layout @isset($history) layout--with-aside @endisset">
        <main class="layout__main">
            @if (session('status'))
                <p class="notice notice--success" role="status">{{ session('status') }}</p>
            @endif

            @yield('content')
        </main>

        @isset($history)
            <aside class="layout__aside">
                @include('partials.history', ['history' => $history])
            </aside>
        @endisset
    </div>

    <footer class="site-footer">
        <div class="container">
            Filmu dati: <a href="https://www.omdbapi.com" rel="noopener" target="_blank">The Open Movie Database</a>
        </div>
    </footer>
</body>
</html>
