<?php

namespace Tests\Feature;

use App\Services\History\SearchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakesOmdb;
use Tests\TestCase;

class MovieSearchTest extends TestCase
{
    use FakesOmdb;
    use RefreshDatabase;

    public function test_home_page_shows_the_search_form(): void
    {
        Http::fake();

        $this->get('/')
            ->assertOk()
            ->assertSee('Filmas nosaukums')
            ->assertSee('Vēl nav neviena meklējuma.');

        Http::assertNothingSent();
    }

    public function test_search_shows_results(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->get('/?q=Matrix')
            ->assertOk()
            ->assertSee('25 rezultāti')
            ->assertSee('The Matrix Reloaded')
            ->assertSee('https://m.media-amazon.com/images/M/matrix.jpg')
            ->assertSee('Nav plakāta')
            ->assertSee(route('movies.show', ['imdbId' => 'tt0133093', 'q' => 'Matrix']));
    }

    public function test_search_is_saved_to_history(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->get('/?q=Matrix')->assertOk();

        $this->assertSame(['Matrix'], app(SearchHistory::class)->recent()->all());
        $this->assertDatabaseHas('search_queries', ['query' => 'Matrix']);
    }

    public function test_history_shows_only_the_last_five_searches(): void
    {
        $this->fakeOmdb($this->searchPayload());

        foreach (['Alien', 'Blade Runner', 'Casablanca', 'Dune', 'Elf', 'Fargo'] as $title) {
            $this->get('/?q='.urlencode($title))->assertOk();
        }

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Fargo', 'Elf', 'Dune', 'Casablanca', 'Blade Runner'])
            ->assertDontSee('Alien');
    }

    public function test_type_and_page_are_passed_to_omdb(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->get('/?q=Matrix&type=series&page=2')
            ->assertOk()
            ->assertSee('2. lapa no 3');

        Http::assertSent(function (Request $request) {
            $query = $this->queryOf($request);

            return $query['type'] === 'series' && $query['page'] === '2';
        });
    }

    public function test_pagination_links_are_shown(): void
    {
        $this->fakeOmdb($this->searchPayload());

        $this->get('/?q=Matrix&page=2')
            ->assertOk()
            ->assertSee(route('search', ['q' => 'Matrix', 'page' => 1]))
            ->assertSee(route('search', ['q' => 'Matrix', 'page' => 3]));
    }

    public function test_empty_results_show_a_message(): void
    {
        $this->fakeOmdb($this->errorPayload('Movie not found!'));

        $this->get('/?q=qwertyuiop')
            ->assertOk()
            ->assertSee('nekas netika atrasts');
    }

    public function test_api_errors_are_shown_to_the_user(): void
    {
        $this->fakeOmdb($this->errorPayload('Too many results.'));

        $this->get('/?q=a')
            ->assertOk()
            ->assertSee('Atrasts pārāk daudz rezultātu');
    }

    public function test_too_long_query_is_rejected_without_calling_the_api(): void
    {
        Http::fake();

        $this->get('/?q='.str_repeat('a', 101))
            ->assertOk()
            ->assertSee('Nosaukums nedrīkst būt garāks par 100 rakstzīmēm.');

        Http::assertNothingSent();
        $this->assertDatabaseCount('search_queries', 0);
    }

    public function test_invalid_type_is_rejected(): void
    {
        Http::fake();

        $this->get('/?q=Matrix&type=cartoon')
            ->assertOk()
            ->assertSee('Nederīgs meklēšanas veids.');

        Http::assertNothingSent();
    }

    public function test_search_output_is_escaped(): void
    {
        $this->fakeOmdb($this->errorPayload('Movie not found!'));

        $this->get('/?q='.urlencode('<script>alert(1)</script>'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_history_can_be_cleared(): void
    {
        app(SearchHistory::class)->record('Matrix');

        $this->delete('/history')
            ->assertRedirect(route('search'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('search_queries', 0);
    }
}
