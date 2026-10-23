<?php

namespace Tests\Support;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

/**
 * Sample OMDb payloads and helpers for faking the API in tests.
 */
trait FakesOmdb
{
    protected function searchPayload(): array
    {
        return [
            'Search' => [
                [
                    'Title' => 'The Matrix',
                    'Year' => '1999',
                    'imdbID' => 'tt0133093',
                    'Type' => 'movie',
                    'Poster' => 'https://m.media-amazon.com/images/M/matrix.jpg',
                ],
                [
                    'Title' => 'The Matrix Reloaded',
                    'Year' => '2003',
                    'imdbID' => 'tt0234215',
                    'Type' => 'movie',
                    'Poster' => 'N/A',
                ],
            ],
            'totalResults' => '25',
            'Response' => 'True',
        ];
    }

    protected function detailsPayload(): array
    {
        return [
            'Title' => 'The Matrix',
            'Year' => '1999',
            'Rated' => 'R',
            'Released' => '31 Mar 1999',
            'Runtime' => '136 min',
            'Genre' => 'Action, Sci-Fi',
            'Director' => 'Lana Wachowski, Lilly Wachowski',
            'Writer' => 'Lilly Wachowski, Lana Wachowski',
            'Actors' => 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss',
            'Plot' => 'A computer hacker learns about the true nature of reality.',
            'Language' => 'English',
            'Country' => 'United States, Australia',
            'Awards' => 'Won 4 Oscars.',
            'Poster' => 'https://m.media-amazon.com/images/M/matrix.jpg',
            'Ratings' => [
                ['Source' => 'Internet Movie Database', 'Value' => '8.7/10'],
                ['Source' => 'Rotten Tomatoes', 'Value' => '83%'],
                ['Source' => 'Metacritic', 'Value' => '73/100'],
            ],
            'Metascore' => '73',
            'imdbRating' => '8.7',
            'imdbVotes' => '2,100,000',
            'imdbID' => 'tt0133093',
            'Type' => 'movie',
            'DVD' => 'N/A',
            'BoxOffice' => '$172,076,928',
            'Production' => 'N/A',
            'Website' => 'N/A',
            'Response' => 'True',
        ];
    }

    protected function errorPayload(string $message): array
    {
        return ['Response' => 'False', 'Error' => $message];
    }

    /**
     * Fake every OMDb request with the given payload.
     */
    protected function fakeOmdb(array $payload, int $status = 200): void
    {
        Http::fake(['*' => Http::response($payload, $status)]);
    }

    /**
     * Query parameters of a faked request.
     *
     * @return array<string, string>
     */
    protected function queryOf(Request $request): array
    {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query;
    }
}
