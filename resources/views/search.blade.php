@extends('layouts.app')

@section('title', $query !== '' ? $query : 'Meklēšana')

@section('content')
    <form method="GET" action="{{ route('search') }}" class="card search-form" role="search">
        <label for="q" class="search-form__label">Filmas nosaukums</label>
        <div class="search-form__row">
            <input
                type="search"
                id="q"
                name="q"
                value="{{ $query }}"
                placeholder="Piemēram, The Matrix"
                maxlength="100"
                autocomplete="off"
                required
                @if ($query === '') autofocus @endif
            >
            <select name="type" aria-label="Veids">
                <option value="">Visi veidi</option>
                <option value="movie" @selected($type === 'movie')>Filmas</option>
                <option value="series" @selected($type === 'series')>Seriāli</option>
                <option value="episode" @selected($type === 'episode')>Epizodes</option>
            </select>
            <button type="submit" class="button">Meklēt</button>
        </div>
    </form>

    @if ($error)
        <p class="notice notice--error" role="alert">{{ $error }}</p>
    @endif

    @if ($result === null && ! $error)
        <div class="empty-state">
            <p>Ievadiet filmas vai seriāla nosaukumu, lai sāktu meklēšanu.</p>
        </div>
    @elseif ($result !== null && $result->isEmpty())
        <div class="empty-state">
            <p>Vaicājumam „<strong>{{ $query }}</strong>” nekas netika atrasts.</p>
            <p class="muted">Pārbaudiet nosaukuma rakstību vai izmēģiniet nosaukumu angļu valodā.</p>
        </div>
    @elseif ($result !== null)
        <p class="results-summary">
            {{ \App\Support\Labels::results($result->total) }} vaicājumam „<strong>{{ $query }}</strong>”
            @if ($result->lastPage() > 1)
                <span class="muted">· {{ $result->page }}. lapa no {{ $result->lastPage() }}</span>
            @endif
        </p>

        <ul class="results">
            @foreach ($result->items as $movie)
                <li>
                    <a
                        href="{{ route('movies.show', array_filter(['imdbId' => $movie->imdbId, 'q' => $query, 'type' => $type, 'page' => $result->page > 1 ? $result->page : null])) }}"
                        class="result-card"
                    >
                        @include('partials.poster', ['poster' => $movie->poster, 'title' => $movie->title])
                        <span class="result-card__body">
                            <span class="result-card__title">{{ $movie->title }}</span>
                            <span class="result-card__meta">
                                {{ $movie->year }} · {{ \App\Support\Labels::type($movie->type) }}
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($result->lastPage() > 1)
            <nav class="pagination" aria-label="Lapas">
                @if ($result->hasPreviousPage())
                    <a class="button button--ghost" rel="prev"
                       href="{{ route('search', array_filter(['q' => $query, 'type' => $type, 'page' => $result->page - 1])) }}">← Iepriekšējā</a>
                @else
                    <span class="button button--ghost is-disabled" aria-disabled="true">← Iepriekšējā</span>
                @endif

                <span class="pagination__info">{{ $result->page }} / {{ $result->lastPage() }}</span>

                @if ($result->hasNextPage())
                    <a class="button button--ghost" rel="next"
                       href="{{ route('search', array_filter(['q' => $query, 'type' => $type, 'page' => $result->page + 1])) }}">Nākamā →</a>
                @else
                    <span class="button button--ghost is-disabled" aria-disabled="true">Nākamā →</span>
                @endif
            </nav>
        @endif
    @endif
@endsection
