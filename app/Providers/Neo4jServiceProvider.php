<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4jServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientInterface::class, function () {
            $host = env('NEO4J_HOST', 'localhost');
            $port = env('NEO4J_PORT', '7687');
            $user = env('NEO4J_USER', 'neo4j');
            $password = env('NEO4J_PASSWORD', 'secret');

            $uri = "bolt://{$user}:{$password}@{$host}:{$port}";

            return ClientBuilder::create()
                ->withDriver('bolt', $uri)
                ->build();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
