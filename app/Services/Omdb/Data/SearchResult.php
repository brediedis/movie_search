<?php

namespace App\Services\Omdb\Data;

/**
 * One page of OMDb search results.
 */
final readonly class SearchResult
{
    /** OMDb always returns at most 10 results per page. */
    public const PER_PAGE = 10;

    /** OMDb only serves pages 1–100. */
    public const MAX_PAGE = 100;

    /**
     * @param  list<MovieSummary>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
    ) {}

    public static function empty(int $page = 1): self
    {
        return new self([], 0, $page);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, int $page): self
    {
        $items = array_map(
            fn (array $item) => MovieSummary::fromArray($item),
            array_values(array_filter($data['Search'] ?? [], 'is_array')),
        );

        return new self($items, (int) ($data['totalResults'] ?? count($items)), $page);
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function lastPage(): int
    {
        return min(self::MAX_PAGE, max(1, (int) ceil($this->total / self::PER_PAGE)));
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->lastPage();
    }
}
