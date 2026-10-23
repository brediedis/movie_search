# Filmu meklētājs — OMDb movie search

A Laravel 13 web app that searches movies through the [OMDb API](https://www.omdbapi.com).

## Features

- **Search by title**, with an optional filter for movies, series or episodes, and pagination (OMDb returns 10 results per page).
- **Last 5 searches** are saved in the database and shown in a sidebar. Repeating a search moves it to the top instead of creating a duplicate (case-insensitive, including Latvian letters). The history can be cleared.
- **Search results** with poster, title, year and type.
- **Movie details page**: poster, full plot, IMDb rating and votes, Rotten Tomatoes / Metacritic ratings, genre, director, writers, actors, release date, runtime, language, country, awards, box office and a link to IMDb.
- Friendly error messages (in Latvian) for "not found", "too many results", an invalid/missing API key, the daily request limit and network failures.
- Successful OMDb responses are cached (1 hour by default) to save the free daily quota.
- Unit and feature tests. The OMDb API is faked in every test, so **the tests need no API key or network access**.

## Requirements

An OMDb API key is required. Request one at <https://www.omdbapi.com/apikey.aspx> and activate it via the link in the email.

## Running with Docker

```bash
git clone git@github.com:brediedis/movie_search.git movie_search
cd movie_search
cp .env.example .env # then set OMDB_API_KEY=your-key in .env
docker compose up --build
```

Open <http://localhost:8000>.

On the first start the container installs Composer dependencies, generates `APP_KEY`, creates the SQLite database and runs the migrations, so it takes a minute. Later starts are quick.

Run the tests:

```bash
docker compose exec app php artisan test
```

The project folder is mounted into the container, so code changes are visible immediately without rebuilding.

To use a different port: `APP_PORT=8080 docker compose up` (and update `APP_URL` in `.env`).

## Configuration

| Variable          | Default                     | Description                                    |
|-------------------|-----------------------------|------------------------------------------------|
| `OMDB_API_KEY`    | *(empty)*                   | Your OMDb API key (required)                   |
| `OMDB_API_URL`    | `https://www.omdbapi.com/`  | API base URL                                   |
| `OMDB_TIMEOUT`    | `10`                        | HTTP timeout in seconds                        |
| `OMDB_CACHE_TTL`  | `3600`                      | How long successful responses are cached (s)   |