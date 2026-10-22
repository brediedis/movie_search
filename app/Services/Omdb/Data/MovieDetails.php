<?php

namespace App\Services\Omdb\Data;

/**
 * Full movie information from an OMDb lookup (?i=).
 */
final readonly class MovieDetails
{
    /**
     * @param  list<array{source: string, value: string}>  $ratings
     */
    public function __construct(
        public string $imdbId,
        public string $title,
        public ?string $year,
        public ?string $type,
        public ?string $rated,
        public ?string $released,
        public ?string $runtime,
        public ?string $genre,
        public ?string $director,
        public ?string $writer,
        public ?string $actors,
        public ?string $plot,
        public ?string $language,
        public ?string $country,
        public ?string $awards,
        public ?string $poster,
        public ?string $imdbRating,
        public ?string $imdbVotes,
        public ?string $metascore,
        public ?string $boxOffice,
        public ?string $totalSeasons,
        public array $ratings,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $ratings = [];

        foreach ($data['Ratings'] ?? [] as $rating) {
            $source = Value::orNull($rating['Source'] ?? null);
            $value = Value::orNull($rating['Value'] ?? null);

            if ($source !== null && $value !== null) {
                $ratings[] = ['source' => $source, 'value' => $value];
            }
        }

        return new self(
            imdbId: (string) ($data['imdbID'] ?? ''),
            title: (string) ($data['Title'] ?? ''),
            year: Value::orNull($data['Year'] ?? null),
            type: Value::orNull($data['Type'] ?? null),
            rated: Value::orNull($data['Rated'] ?? null),
            released: Value::orNull($data['Released'] ?? null),
            runtime: Value::orNull($data['Runtime'] ?? null),
            genre: Value::orNull($data['Genre'] ?? null),
            director: Value::orNull($data['Director'] ?? null),
            writer: Value::orNull($data['Writer'] ?? null),
            actors: Value::orNull($data['Actors'] ?? null),
            plot: Value::orNull($data['Plot'] ?? null),
            language: Value::orNull($data['Language'] ?? null),
            country: Value::orNull($data['Country'] ?? null),
            awards: Value::orNull($data['Awards'] ?? null),
            poster: Value::orNull($data['Poster'] ?? null),
            imdbRating: Value::orNull($data['imdbRating'] ?? null),
            imdbVotes: Value::orNull($data['imdbVotes'] ?? null),
            metascore: Value::orNull($data['Metascore'] ?? null),
            boxOffice: Value::orNull($data['BoxOffice'] ?? null),
            totalSeasons: Value::orNull($data['totalSeasons'] ?? null),
            ratings: $ratings,
        );
    }

    /**
     * The IMDb rating as a 0–100 percentage, for the rating bar.
     */
    public function imdbRatingPercent(): ?int
    {
        if ($this->imdbRating === null || ! is_numeric($this->imdbRating)) {
            return null;
        }

        return (int) round(min(10, max(0, (float) $this->imdbRating)) * 10);
    }

    public function imdbUrl(): string
    {
        return 'https://www.imdb.com/title/'.$this->imdbId.'/';
    }
}
