<?php

require_once 'vendor/autoload.php';

use Laudis\Neo4j\Authentication\Authenticate;
use Laudis\Neo4j\ClientBuilder;

echo "Testing Neo4j Connection...\n";
echo "=============================\n";

// Read environment variables from .env file
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && ! str_starts_with($line, '#')) {
            [$name, $value] = explode('=', $line, 2);
            putenv(trim($name).'='.trim($value));
        }
    }
}

// Get credentials from environment
$host = getenv('NEO4J_HOST') ?: 'localhost';
// If running outside Docker, use localhost instead of service name
if ($host === 'neo4j') {
    $host = 'localhost';
}
$port = getenv('NEO4J_PORT') ?: '7687';
$user = getenv('NEO4J_USER') ?: 'neo4j';
$password = getenv('NEO4J_PASSWORD') ?: 'password';
$database = getenv('NEO4J_DATABASE') ?: 'neo4j';

echo "Configuration:\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Username: $user\n";
echo "Database: $database\n";
echo "\n";

try {
    echo "1. Creating Neo4j client...\n";

    $client = ClientBuilder::create()
        ->withDriver('default', "bolt://$host:$port", Authenticate::basic($user, $password))
        ->build();

    echo "✅ Client created successfully!\n\n";

    echo "2. Testing connection with simple query...\n";
    $result = $client->run('RETURN "Hello Neo4j!" as message, timestamp() as time');
    $record = $result->first();

    echo "✅ Connection successful!\n";
    echo 'Message: '.$record->get('message')."\n";
    echo 'Timestamp: '.$record->get('time')."\n\n";

    echo "3. Testing database info...\n";
    $result = $client->run('CALL db.info()');
    $record = $result->first();
    echo "✅ Database info retrieved!\n";
    echo 'Database: '.$record->get('name')."\n\n";

    echo "4. Testing node creation (Neuron)...\n";
    $createQuery = '
        CREATE (n:Neuron {
            name: $name,
            layer: $layer,
            activation_level: $activation_level,
            threshold: $threshold,
            created_at: datetime()
        })
        RETURN n
    ';

    $result = $client->run($createQuery, [
        'name' => 'Test Connection Neuron',
        'layer' => 4,
        'activation_level' => 0.0,
        'threshold' => 0.5,
    ]);

    $record = $result->first();
    $neuron = $record->get('n');

    echo "✅ Neuron created successfully!\n";
    echo 'ID: '.$neuron->getId()."\n";
    echo 'Properties: '.json_encode($neuron->getProperties()->toArray())."\n\n";

    echo "5. Testing node query...\n";
    $queryResult = $client->run(
        'MATCH (n:Neuron) WHERE n.layer = $layer RETURN n, count(n) as total',
        ['layer' => 4]
    );

    $queryRecord = $queryResult->first();
    echo "✅ Query executed successfully!\n";
    echo 'Found '.$queryRecord->get('total')." neurons in layer 4\n\n";

    echo "6. Testing cleanup...\n";
    $deleteResult = $client->run(
        'MATCH (n:Neuron {name: $name}) DELETE n',
        ['name' => 'Test Connection Neuron']
    );

    $deletedCount = $deleteResult->getSummary()->getCounters()->nodesDeleted();
    echo "✅ Cleanup completed!\n";
    echo 'Deleted '.$deletedCount." test nodes\n\n";

    echo "🎉 All tests passed! Neo4j connection is working perfectly!\n";
    echo "✅ GraphCriteria infrastructure is ready to use.\n";

} catch (Exception $e) {
    echo '❌ Connection failed: '.$e->getMessage()."\n";
    echo 'Error class: '.get_class($e)."\n";

    if ($e->getPrevious()) {
        echo 'Previous error: '.$e->getPrevious()->getMessage()."\n";
    }

    echo "\nTroubleshooting:\n";
    echo "1. Check if Neo4j is running:\n";
    echo "   docker ps | grep neo4j\n";
    echo "   OR netstat -ln | grep 7687\n\n";
    echo "2. Try connecting via Neo4j Browser:\n";
    echo "   http://$host:7474\n\n";
    echo "3. Check Docker logs if using Docker:\n";
    echo "   docker logs <neo4j-container-name>\n\n";
    echo "4. Verify credentials in .env file\n";
}
