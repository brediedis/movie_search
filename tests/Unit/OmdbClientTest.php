<?php

namespace Tests\Unit;

use App\Services\Omdb\Exceptions\MovieNotFoundException;
use App\Services\Omdb\Exceptions\OmdbException;
use App\Services\Omdb\OmdbClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakesOmdb;
use Tests\TestCase;

class OmdbClientTest extends TestCase
{
    use FakesOmdb;

    private function client(?string $apiKey = 'test-key'): OmdbClient
    {
        return new OmdbClient(apiKey: $apiKey, baseUrl: 'https://www.omdbapi.com/');
    }

    public function test_search_sends_the_expected_query_parameters(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->client()->search('  The Matrix ', page: 2, type: 'movie');

        Http::assertSent(function (Request $request) {
            $query = $this->queryOf($request);

            return str_starts_with($request->url(), 'https://www.omdbapi.com/')
                && $query['apikey'] === 'test-key'
                && $query['s'] === 'The Matrix'
                && $query['page'] === '2'
                && $query['type'] === 'movie';
        });
    }

    public function test_search_ignores_unknown_type(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->client()->search('Matrix', type: 'cartoon');

        Http::assertSent(fn (Request $request) => ! array_key_exists('type', $this->queryOf($request)));
    }

    public function test_search_maps_results(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $result = $this->client()->search('Matrix');

        $this->assertCount(2, $result->items);
        $this->assertSame(25, $result->total);
        $this->assertSame(1, $result->page);
        $this->assertSame(3, $result->lastPage());
        $this->assertTrue($result->hasNextPage());
        $this->assertFalse($result->hasPreviousPage());

        $first = $result->items[0];
        $this->assertSame('tt0133093', $first->imdbId);
        $this->assertSame('The Matrix', $first->title);
        $this->assertSame('1999', $first->year);
        $this->assertSame('movie', $first->type);
        $this->assertSame('https://m.media-amazon.com/images/M/matrix.jpg', $first->poster);

        // OMDb uses "N/A" when there is no poster.
        $this->assertNull($result->items[1]->poster);
    }

    public function test_search_returns_empty_result_when_nothing_is_found(): void
    {
        $this->fakeOmdb($this->errorPayload('Movie not found!'));

        $result = $this->client()->search('asdfghjkl');

        $this->assertTrue($result->isEmpty());
        $this->assertSame(0, $result->total);
    }

    public function test_search_throws_on_api_error(): void
    {
        $this->fakeOmdb($this->errorPayload('Too many results.'));

        $this->expectException(OmdbException::class);
        $this->expectExceptionMessage('Too many results.');

        $this->client()->search('a');
    }

    public function test_invalid_api_key_is_reported(): void
    {
        $this->fakeOmdb($this->errorPayload('Invalid API key!'), 401);

        try {
            $this->client()->search('Matrix');
            $this->fail('Expected an OmdbException.');
        } catch (OmdbException $e) {
            $this->assertNotInstanceOf(MovieNotFoundException::class, $e);
            $this->assertStringContainsString('API atslēga', $e->userMessage());
        }
    }

    public function test_server_error_without_json_throws(): void
    {
        Http::fake(['*' => Http::response('Service Unavailable', 503)]);

        $this->expectException(OmdbException::class);

        $this->client()->search('Matrix');
    }

    public function test_connection_failure_throws(): void
    {
        Http::fake(fn () => throw new ConnectionException('Connection timed out'));

        $this->expectException(OmdbException::class);

        $this->client()->search('Matrix');
    }

    public function test_missing_api_key_throws_without_calling_the_api(): void
    {
        Http::fake();

        try {
            $this->client(apiKey: '')->search('Matrix');
            $this->fail('Expected an OmdbException.');
        } catch (OmdbException) {
            Http::assertNothingSent();
        }
    }

    public function test_find_maps_full_details(): void
    {
        $this->fakeOmdb($this->detailsPayload());

        $movie = $this->client()->find('tt0133093');

        Http::assertSent(fn (Request $request) => $this->queryOf($request)['i'] === 'tt0133093'
            && $this->queryOf($request)['plot'] === 'full');

        $this->assertSame('The Matrix', $movie->title);
        $this->assertSame('8.7', $movie->imdbRating);
        $this->assertSame(87, $movie->imdbRatingPercent());
        $this->assertSame('Action, Sci-Fi', $movie->genre);
        $this->assertSame('A computer hacker learns about the true nature of reality.', $movie->plot);
        $this->assertSame('https://www.imdb.com/title/tt0133093/', $movie->imdbUrl());
        $this->assertCount(3, $movie->ratings);
        $this->assertSame(['source' => 'Rotten Tomatoes', 'value' => '83%'], $movie->ratings[1]);
    }

    public function test_find_throws_not_found_for_unknown_id(): void
    {
        $this->fakeOmdb($this->errorPayload('Incorrect IMDb ID.'));

        $this->expectException(MovieNotFoundException::class);

        $this->client()->find('tt9999999');
    }

    public function test_successful_responses_are_cached(): void
    {
        $this->fakeOmdb($this->detailsPayload());

        $this->client()->find('tt0133093');
        $this->client()->find('tt0133093');

        Http::assertSentCount(1);
    }

    public function test_errors_are_not_cached(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push($this->errorPayload('Request limit reached!'), 401)
            ->push($this->detailsPayload()),
        ]);

        try {
            $this->client()->find('tt0133093');
        } catch (OmdbException) {
            // expected on the first call
        }

        $movie = $this->client()->find('tt0133093');

        $this->assertSame('The Matrix', $movie->title);
        Http::assertSentCount(2);
    }
}
