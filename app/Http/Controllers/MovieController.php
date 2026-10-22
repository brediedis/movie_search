<?php

namespace App\Http\Controllers;

use App\Services\History\SearchHistory;
use App\Services\Omdb\Exceptions\MovieNotFoundException;
use App\Services\Omdb\Exceptions\OmdbException;
use App\Services\Omdb\OmdbClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieController extends Controller
{
    public function show(Request $request, string $imdbId, OmdbClient $omdb, SearchHistory $history): View
    {
        $movie = null;
        $error = null;

        try {
            $movie = $omdb->find($imdbId);
        } catch (MovieNotFoundException) {
            abort(404, 'Filma netika atrasta.');
        } catch (OmdbException $e) {
            report($e);
            $error = $e->userMessage();
        }

        return view('movies.show', [
            'movie' => $movie,
            'error' => $error,
            'history' => $history->recent(),
            // Lets the "back" link return to the same search results page.
            'backQuery' => array_filter(
                $request->only(['q', 'type', 'page']),
                fn ($value) => is_string($value) && $value !== '',
            ),
        ]);
    }
}
