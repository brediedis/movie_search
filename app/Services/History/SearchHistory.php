<?php

namespace App\Services\History;

use App\Models\SearchQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Keeps the most recent search queries (5 by default).
 *
 * Repeating a search does not create a duplicate entry: the existing
 * entry is moved to the top instead. Comparison ignores letter case
 * and extra whitespace.
 */
class SearchHistory
{
    public const LIMIT = 5;

    public function __construct(private readonly int $limit = self::LIMIT) {}

    public function record(string $query): void
    {
        $query = Str::squish($query);

        if ($query === '') {
            return;
        }

        $query = Str::limit($query, 100, '');
        $normalized = mb_strtolower($query);

        DB::transaction(function () use ($query, $normalized) {
            SearchQuery::query()->where('normalized', $normalized)->delete();

            SearchQuery::query()->create([
                'query' => $query,
                'normalized' => $normalized,
            ]);

            $keep = SearchQuery::query()
                ->orderByDesc('id')
                ->limit($this->limit)
                ->pluck('id');

            SearchQuery::query()->whereNotIn('id', $keep)->delete();
        });
    }

    /**
     * Most recent queries first.
     *
     * @return Collection<int, string>
     */
    public function recent(): Collection
    {
        return SearchQuery::query()
            ->orderByDesc('id')
            ->limit($this->limit)
            ->pluck('query');
    }

    public function clear(): void
    {
        SearchQuery::query()->delete();
    }
}
