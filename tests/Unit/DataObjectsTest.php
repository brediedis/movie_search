<?php

namespace Tests\Unit;

use App\Services\Omdb\Data\MovieDetails;
use App\Services\Omdb\Data\SearchResult;
use App\Services\Omdb\Data\Value;
use App\Support\Labels;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests: no Laravel application is booted.
 */
class DataObjectsTest extends TestCase
{
    public function test_na_and_empty_values_become_null(): void
    {
        $this->assertNull(Value::orNull('N/A'));
        $this->assertNull(Value::orNull('n/a'));
        $this->assertNull(Value::orNull('  '));
        $this->assertNull(Value::orNull(null));
        $this->assertSame('8.7', Value::orNull(' 8.7 '));
    }

    public function test_movie_details_handle_missing_fields(): void
    {
        $movie = MovieDetails::fromArray([
            'Title' => 'Obscure Film',
            'imdbID' => 'tt0000001',
            'imdbRating' => 'N/A',
            'Plot' => 'N/A',
            'Ratings' => [['Source' => 'Metacritic', 'Value' => 'N/A']],
        ]);

        $this->assertSame('Obscure Film', $movie->title);
        $this->assertNull($movie->imdbRating);
        $this->assertNull($movie->imdbRatingPercent());
        $this->assertNull($movie->plot);
        $this->assertSame([], $movie->ratings);
    }

    public function test_search_result_page_count_is_capped_at_omdb_maximum(): void
    {
        $result = new SearchResult([], total: 5000, page: 1);

        $this->assertSame(100, $result->lastPage());
    }

    public function test_search_result_with_single_page(): void
    {
        $result = new SearchResult([], total: 7, page: 1);

        $this->assertSame(1, $result->lastPage());
        $this->assertFalse($result->hasNextPage());
        $this->assertFalse($result->hasPreviousPage());
    }

    #[DataProvider('resultCounts')]
    public function test_latvian_result_count_label(int $count, string $expected): void
    {
        $this->assertSame($expected, Labels::results($count));
    }

    public static function resultCounts(): array
    {
        return [
            [1, '1 rezultāts'],
            [2, '2 rezultāti'],
            [11, '11 rezultāti'],
            [21, '21 rezultāts'],
            [1234, '1 234 rezultāti'],
        ];
    }

    public function test_type_labels(): void
    {
        $this->assertSame('Filma', Labels::type('movie'));
        $this->assertSame('Seriāls', Labels::type('series'));
        $this->assertSame('unknown', Labels::type('unknown'));
    }
}
