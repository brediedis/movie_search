<section class="card history" aria-labelledby="history-title">
    <h2 id="history-title" class="history__title">Pēdējie meklējumi</h2>

    @if ($history->isEmpty())
        <p class="muted">Vēl nav neviena meklējuma.</p>
    @else
        <ol class="history__list">
            @foreach ($history as $item)
                <li>
                    <a href="{{ route('search', ['q' => $item]) }}" class="history__link">{{ $item }}</a>
                </li>
            @endforeach
        </ol>

        <form method="POST" action="{{ route('history.clear') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="button button--ghost button--small">Notīrīt vēsturi</button>
        </form>
    @endif
</section>
