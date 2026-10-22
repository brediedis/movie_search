@extends('layouts.app')

@section('title', $movie?->title ?? 'Filma')

@section('content')
    <a href="{{ route('search', $backQuery) }}" class="back-link">← Atpakaļ uz meklēšanu</a>

    @if ($error)
        <p class="notice notice--error" role="alert">{{ $error }}</p>
    @endif

    @if ($movie)
        <article class="card movie">
            <div class="movie__poster">
                @include('partials.poster', ['poster' => $movie->poster, 'title' => $movie->title])
            </div>

            <div class="movie__body">
                <h1 class="movie__title">{{ $movie->title }}</h1>

                <p class="movie__meta">
                    @foreach (array_filter([$movie->year, \App\Support\Labels::type($movie->type), $movie->rated, $movie->runtime]) as $part)
                        <span>{{ $part }}</span>
                    @endforeach
                </p>

                @if ($movie->genre)
                    <ul class="tags" aria-label="Žanri">
                        @foreach (explode(',', $movie->genre) as $genre)
                            <li class="tag">{{ trim($genre) }}</li>
                        @endforeach
                    </ul>
                @endif

                @if ($movie->imdbRating)
                    <div class="rating" aria-label="IMDb vērtējums {{ $movie->imdbRating }} no 10">
                        <span class="rating__badge">IMDb</span>
                        <span class="rating__score">{{ $movie->imdbRating }}<small>/10</small></span>
                        @if ($movie->imdbRatingPercent() !== null)
                            <span class="rating__bar" aria-hidden="true">
                                <span style="width: {{ $movie->imdbRatingPercent() }}%"></span>
                            </span>
                        @endif
                        @if ($movie->imdbVotes)
                            <span class="muted">{{ $movie->imdbVotes }} balsis</span>
                        @endif
                    </div>
                @endif

                @if ($movie->plot)
                    <h2 class="movie__section-title">Apraksts</h2>
                    <p class="movie__plot">{{ $movie->plot }}</p>
                @endif

                @php
                    $facts = array_filter([
                        'Režisors' => $movie->director,
                        'Scenārists' => $movie->writer,
                        'Aktieri' => $movie->actors,
                        'Pirmizrāde' => $movie->released,
                        'Valoda' => $movie->language,
                        'Valsts' => $movie->country,
                        'Sezonas' => $movie->totalSeasons,
                        'Apbalvojumi' => $movie->awards,
                        'Kases ieņēmumi' => $movie->boxOffice,
                        'Metascore' => $movie->metascore,
                    ]);
                @endphp

                @if ($facts !== [])
                    <dl class="facts">
                        @foreach ($facts as $label => $value)
                            <div class="facts__row">
                                <dt>{{ $label }}</dt>
                                <dd>{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif

                @if (count($movie->ratings) > 0)
                    <h2 class="movie__section-title">Vērtējumi</h2>
                    <ul class="ratings">
                        @foreach ($movie->ratings as $rating)
                            <li>
                                <span class="ratings__value">{{ $rating['value'] }}</span>
                                <span class="muted">{{ $rating['source'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <a href="{{ $movie->imdbUrl() }}" class="button button--ghost" target="_blank" rel="noopener">
                    Skatīt IMDb ↗
                </a>
            </div>
        </article>
    @endif
@endsection
