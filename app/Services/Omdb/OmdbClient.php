<?php

namespace App\Services\Omdb;

use App\Services\Omdb\Data\MovieDetails;
use App\Services\Omdb\Data\SearchResult;
use App\Services\Omdb\Exceptions\MovieNotFoundException;
use App\Services\Omdb\Exceptions\OmdbException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the OMDb API (https://www.omdbapi.com).
 *
 * Successful responses are cached so that paging back and forth or
 * re-opening a movie does not use up the daily request quota.
 */
class OmdbClient
{
    /** Search result types supported by OMDb's "type" parameter. */
    public const TYPES = ['movie', 'series', 'episode'];

    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $baseUrl = 'https://www.omdbapi.com/',
        private readonly int $timeout = 10,
        private readonly int $cacheTtl = 3600,
    ) {}

    /**
     * Search movies by title.
     *
     * @throws OmdbException
     */
    public function search(string $title, int $page = 1, ?string $type = null): SearchResult
    {
        $page = max(1, min(SearchResult::MAX_PAGE, $page));

        $query = ['s' => trim($title), 'page' => $page];

        if ($type !== null && in_array($type, self::TYPES, true)) {
            $query['type'] = $type;
        }

        try {
            return SearchResult::fromArray($this->request($query), $page);
        } catch (MovieNotFoundException) {
            // "Movie not found!" is a normal, empty search result.
            return SearchResult::empty($page);
        }
    }

    /**
     * Get full details for one movie by its IMDb ID (e.g. "tt0133093").
     *
     * @throws MovieNotFoundException
     * @throws OmdbException
     */
    public function find(string $imdbId): MovieDetails
    {
        return MovieDetails::fromArray($this->request(['i' => $imdbId, 'plot' => 'full']));
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array<string, mixed>
     *
     * @throws OmdbException
     */
    private function request(array $query): array
    {
        if (blank($this->apiKey)) {
            throw new OmdbException('OMDb API key is not configured.');
        }

        ksort($query);
        $cacheKey = 'omdb:'.sha1(http_build_query($query));

        // Only successful responses get cached: exceptions thrown inside
        // the callback skip the cache write.
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($query) {
            try {
                $response = Http::timeout($this->timeout)
                    ->acceptJson()
                    ->get($this->baseUrl, ['apikey' => $this->apiKey, ...$query]);
            } catch (ConnectionException $e) {
                throw new OmdbException('Could not connect to OMDb: '.$e->getMessage(), previous: $e);
            }

            $data = $response->json();

            if (! is_array($data)) {
                throw new OmdbException("Unexpected OMDb response (HTTP {$response->status()}).");
            }

            if (($data['Response'] ?? 'False') !== 'True') {
                $error = (string) ($data['Error'] ?? 'Unknown OMDb error.');

                throw $this->isNotFound($error)
                    ? new MovieNotFoundException($error)
                    : new OmdbException($error);
            }

            if ($response->failed()) {
                throw new OmdbException("OMDb request failed (HTTP {$response->status()}).");
            }

            return $data;
        });
    }

    private function isNotFound(string $error): bool
    {
        $error = strtolower($error);

        return str_contains($error, 'not found') || str_contains($error, 'incorrect imdb id');
    }
}
