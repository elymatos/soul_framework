<?php

namespace App\Services\Neo4j;

use Illuminate\Support\Facades\Log;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Exception\Neo4jException;
use RuntimeException;

class ConnectionService
{
    private static array $connections = [];

    private static array $config = [];

    /**
     * Get a Neo4j client connection
     */
    public static function connection(?string $name = null): ClientInterface
    {
        $name = $name ?? config('neo4j.default');

        if (! isset(self::$connections[$name])) {
            self::$connections[$name] = self::createConnection($name);
        }

        return self::$connections[$name];
    }

    /**
     * Create a new Neo4j connection
     */
    private static function createConnection(string $name): ClientInterface
    {
        $config = config("neo4j.connections.{$name}");

        if (! $config) {
            throw new RuntimeException("Neo4j connection [{$name}] not configured.");
        }

        try {
            $builder = ClientBuilder::create();

            // Build connection URI based on driver type
            $uri = self::buildUri($config);

            // Add connection with authentication
            $builder = $builder->withDriver(
                $name,
                $uri,
                \Laudis\Neo4j\Authentication\Authenticate::basic(
                    $config['username'],
                    $config['password']
                )
            );

            // Set default database if specified
            if (! empty($config['database']) && $config['database'] !== 'neo4j') {
                $builder = $builder->withDefaultDriver($name);
            }

            $client = $builder->build();

            // Test connection
            self::testConnection($client, $name);

            self::logConnection($name, $config);

            return $client;

        } catch (Neo4jException $e) {
            self::logConnectionError($name, $e);
            throw new RuntimeException("Failed to connect to Neo4j [{$name}]: ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * Build connection URI from config
     */
    private static function buildUri(array $config): string
    {
        $driver = $config['driver'] ?? 'bolt';
        $host = $config['host'];
        $port = $config['port'];

        // Handle Docker service name resolution for development
        if ($host === 'neo4j' && ! self::isRunningInContainer()) {
            $host = 'localhost';
        }

        return "{$driver}://{$host}:{$port}";
    }

    /**
     * Check if we're running inside a Docker container
     */
    private static function isRunningInContainer(): bool
    {
        return file_exists('/.dockerenv') ||
               (file_exists('/proc/1/cgroup') &&
                str_contains(file_get_contents('/proc/1/cgroup'), 'docker'));
    }

    /**
     * Test the Neo4j connection
     */
    private static function testConnection(ClientInterface $client, string $name): void
    {
        try {
            $result = $client->run('RETURN 1 as test');
            $record = $result->first();

            if (! $record || $record->get('test') !== 1) {
                throw new RuntimeException("Connection test failed for Neo4j [{$name}]");
            }
        } catch (Neo4jException $e) {
            throw new RuntimeException("Connection test failed for Neo4j [{$name}]: ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * Log successful connection
     */
    private static function logConnection(string $name, array $config): void
    {
        if (config('neo4j.logging.enabled')) {
            Log::channel(config('neo4j.logging.channel', 'daily'))
                ->info('Neo4j connection established', [
                    'connection' => $name,
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'driver' => $config['driver'],
                ]);
        }
    }

    /**
     * Log connection error
     */
    private static function logConnectionError(string $name, Neo4jException $e): void
    {
        Log::channel(config('neo4j.logging.channel', 'daily'))
            ->error('Neo4j connection failed', [
                'connection' => $name,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
    }

    /**
     * Close all connections
     */
    public static function closeAll(): void
    {
        self::$connections = [];
    }

    /**
     * Get connection statistics
     */
    public static function getStats(): array
    {
        return [
            'active_connections' => count(self::$connections),
            'connection_names' => array_keys(self::$connections),
        ];
    }

    /**
     * Check if connection exists
     */
    public static function hasConnection(?string $name = null): bool
    {
        $name = $name ?? config('neo4j.default');

        return isset(self::$connections[$name]);
    }
}
