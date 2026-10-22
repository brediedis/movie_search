<?php

namespace App\Services\Omdb\Data;

/**
 * A single item from an OMDb search response (?s=).
 */
final readonly class MovieSummary
{
    public function __construct(
        public string $imdbId,
        public string $title,
        public string $year,
        public string $type,
        public ?string $poster,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            imdbId: (string) ($data['imdbID'] ?? ''),
            title: (string) ($data['Title'] ?? ''),
            year: (string) ($data['Year'] ?? ''),
            type: (string) ($data['Type'] ?? ''),
            poster: Value::orNull($data['Poster'] ?? null),
        );
    }
}
