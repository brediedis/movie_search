<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakesOmdb;
use Tests\TestCase;

class MovieDetailsTest extends TestCase
{
    use FakesOmdb;
    use RefreshDatabase;

    public function test_it_shows_extended_movie_information(): void
    {
        $this->fakeOmdb($this->detailsPayload());

        $this->get('/movies/tt0133093')
            ->assertOk()
            ->assertSee('The Matrix')
            ->assertSee('https://m.media-amazon.com/images/M/matrix.jpg')
            ->assertSee('A computer hacker learns about the true nature of reality.')
            ->assertSee('8.7')
            ->assertSee('2,100,000')
            ->assertSee('Lana Wachowski, Lilly Wachowski')
            ->assertSee('Keanu Reeves')
            ->assertSee('Rotten Tomatoes')
            ->assertSee('83%')
            ->assertSee('Sci-Fi')
            ->assertSee('https://www.imdb.com/title/tt0133093/');
    }

    public function test_back_link_returns_to_the_same_search(): void
    {
        $this->fakeOmdb($this->detailsPayload());

        $this->get('/movies/tt0133093?q=Matrix&page=2')
            ->assertOk()
            ->assertSee(route('search', ['q' => 'Matrix', 'page' => 2]));
    }

    public function test_unknown_movie_returns_404(): void
    {
        $this->fakeOmdb($this->errorPayload('Incorrect IMDb ID.'));

        $this->get('/movies/tt9999999')
            ->assertNotFound()
            ->assertSee('Lapa vai filma netika atrasta');
    }

    public function test_malformed_imdb_id_returns_404_without_calling_the_api(): void
    {
        Http::fake();

        $this->get('/movies/not-an-id')->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_api_failure_shows_an_error_message(): void
    {
        Http::fake(['*' => Http::response('Bad Gateway', 502)]);

        $this->get('/movies/tt0133093')
            ->assertOk()
            ->assertSee('Neizdevās iegūt datus no OMDb servisa');
    }
}
