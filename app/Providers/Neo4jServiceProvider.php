<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;

class Neo4jServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ClientInterface::class, function ($app) {
            $host = env('NEO4J_HOST', 'localhost');
            $port = env('NEO4J_PORT', '7687');
            $user = env('NEO4J_USERNAME', 'neo4j');
            $password = env('NEO4J_PASSWORD', 'secret');

            return ClientBuilder::create()
                ->withDriver('bolt', "bolt://{$host}:{$port}", 
                    Authenticate::basic($user, $password))
                ->withDefaultDriver('bolt')
                ->build();
        });

        // Also bind to 'neo4j' for easier access
        $this->app->bind('neo4j', ClientInterface::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
