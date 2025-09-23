<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Neo4j Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the Neo4j connections below you wish
    | to use as your default connection for graph database operations.
    |
    */

    'default' => env('NEO4J_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Neo4j Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the Neo4j connections defined for your application.
    | Each connection uses the laudis/neo4j-php-client driver.
    |
    */

    'connections' => [

        'default' => [
            'driver' => 'bolt',
            'host' => env('NEO4J_HOST', 'localhost'),
            'port' => env('NEO4J_PORT', 7687),
            'username' => env('NEO4J_USER', 'neo4j'),
            'password' => env('NEO4J_PASSWORD', 'password'),
            'database' => env('NEO4J_DATABASE', 'neo4j'),
            'timeout' => env('NEO4J_TIMEOUT', 30),
            'user_agent' => 'SOUL Framework/1.0',
        ],

        'http' => [
            'driver' => 'http',
            'host' => env('NEO4J_HTTP_HOST', 'localhost'),
            'port' => env('NEO4J_HTTP_PORT', 7474),
            'username' => env('NEO4J_USER', 'neo4j'),
            'password' => env('NEO4J_PASSWORD', 'password'),
            'database' => env('NEO4J_DATABASE', 'neo4j'),
            'timeout' => env('NEO4J_TIMEOUT', 30),
            'user_agent' => 'SOUL Framework/1.0',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Query Logging
    |--------------------------------------------------------------------------
    |
    | Controls whether Neo4j queries should be logged.
    | Options: 'none', 'debug', 'info'
    |
    */

    'logging' => [
        'enabled' => env('NEO4J_LOG_QUERIES', false),
        'level' => env('NEO4J_LOG_LEVEL', 'debug'),
        'channel' => env('NEO4J_LOG_CHANNEL', 'daily'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pool Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for connection pooling and retry behavior.
    |
    */

    'pool' => [
        'max_connections' => env('NEO4J_MAX_CONNECTIONS', 10),
        'connection_timeout' => env('NEO4J_CONNECTION_TIMEOUT', 30),
        'max_transaction_retry_time' => env('NEO4J_MAX_RETRY_TIME', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cortical Network Schema Configuration
    |--------------------------------------------------------------------------
    |
    | Default settings for the cortical network implementation.
    |
    */

    'cortical_network' => [
        'node_labels' => [
            'neuron' => 'Neuron',
        ],
        'relationship_types' => [
            'connects_to' => 'CONNECTS_TO',
            'activates' => 'ACTIVATES',
            'inhibits' => 'INHIBITS',
        ],
        'layers' => [
            'input' => 4,
            'processing' => 23,
            'output' => 5,
        ],
        'default_properties' => [
            'activation_level' => 0.0,
            'threshold' => 0.5,
        ],
    ],

];
