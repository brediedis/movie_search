<?php

namespace App\Providers;

use App\Services\Omdb\OmdbClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OmdbClient::class, fn ($app) => new OmdbClient(
            apiKey: $app['config']->get('services.omdb.key'),
            baseUrl: $app['config']->get('services.omdb.url', 'https://www.omdbapi.com/'),
            timeout: (int) $app['config']->get('services.omdb.timeout', 10),
            cacheTtl: (int) $app['config']->get('services.omdb.cache_ttl', 3600),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
