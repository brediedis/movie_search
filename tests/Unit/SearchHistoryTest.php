<?php

namespace Tests\Unit;

use App\Services\History\SearchHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchHistoryTest extends TestCase
{
    use RefreshDatabase;

    private SearchHistory $history;

    protected function setUp(): void
    {
        parent::setUp();

        $this->history = new SearchHistory;
    }

    public function test_it_is_empty_by_default(): void
    {
        $this->assertTrue($this->history->recent()->isEmpty());
    }

    public function test_it_returns_most_recent_queries_first(): void
    {
        $this->history->record('Matrix');
        $this->history->record('Alien');
        $this->history->record('Up');

        $this->assertSame(['Up', 'Alien', 'Matrix'], $this->history->recent()->all());
    }

    public function test_it_keeps_only_the_last_five_queries(): void
    {
        foreach (['One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven'] as $query) {
            $this->history->record($query);
        }

        $this->assertSame(['Seven', 'Six', 'Five', 'Four', 'Three'], $this->history->recent()->all());
        $this->assertDatabaseCount('search_queries', 5);
    }

    public function test_repeating_a_query_moves_it_to_the_top_without_duplicates(): void
    {
        $this->history->record('Matrix');
        $this->history->record('Alien');
        $this->history->record('  matrix  ');

        $this->assertSame(['matrix', 'Alien'], $this->history->recent()->all());
    }

    public function test_duplicate_detection_handles_latvian_characters(): void
    {
        $this->history->record('Ēnu ŠĶĒRSLIS');
        $this->history->record('ēnu šķērslis');

        $this->assertSame(['ēnu šķērslis'], $this->history->recent()->all());
    }

    public function test_whitespace_is_normalised_and_blank_queries_are_ignored(): void
    {
        $this->history->record("  The   Dark\tKnight ");
        $this->history->record('   ');

        $this->assertSame(['The Dark Knight'], $this->history->recent()->all());
    }

    public function test_long_queries_are_truncated(): void
    {
        $this->history->record(str_repeat('a', 150));

        $this->assertSame(100, mb_strlen($this->history->recent()->first()));
    }

    public function test_it_can_be_cleared(): void
    {
        $this->history->record('Matrix');
        $this->history->clear();

        $this->assertTrue($this->history->recent()->isEmpty());
    }

    public function test_limit_is_configurable(): void
    {
        $history = new SearchHistory(limit: 2);

        $history->record('One');
        $history->record('Two');
        $history->record('Three');

        $this->assertSame(['Three', 'Two'], $history->recent()->all());
    }
}
