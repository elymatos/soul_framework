<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel to load config
$app = require_once 'bootstrap/app.php';
$app->loadEnvironmentFrom('.env');

use App\Database\GraphCriteria;
use App\Services\Neo4j\ConnectionService;

echo "Testing Neo4j Connection...\n";
echo "=============================\n";

// Display configuration
echo "Configuration:\n";
echo 'Host: '.config('neo4j.connections.default.host')."\n";
echo 'Port: '.config('neo4j.connections.default.port')."\n";
echo 'Username: '.config('neo4j.connections.default.username')."\n";
echo 'Database: '.config('neo4j.connections.default.database')."\n";
echo "\n";

try {
    echo "1. Testing basic connection...\n";
    $client = ConnectionService::connection();
    echo "✅ Connection established successfully!\n\n";

    echo "2. Testing simple query...\n";
    $result = $client->run('RETURN "Hello Neo4j!" as message, timestamp() as time');
    $record = $result->first();
    echo "✅ Query executed successfully!\n";
    echo 'Message: '.$record->get('message')."\n";
    echo 'Timestamp: '.$record->get('time')."\n\n";

    echo "3. Testing GraphCriteria with a simple operation...\n";

    // Test creating a neuron
    $neuron = GraphCriteria::createNode('Neuron', [
        'name' => 'Test Connection Neuron',
        'layer' => 4,
        'activation_level' => 0.0,
        'threshold' => 0.5,
    ]);

    echo '✅ Created neuron with ID: '.$neuron->id."\n";
    echo 'Properties: '.json_encode($neuron->properties)."\n\n";

    echo "4. Testing query operations...\n";
    $neurons = GraphCriteria::node('Neuron')
        ->where('n.layer', '=', 4)
        ->limit(5)
        ->get();

    echo '✅ Found '.$neurons->count()." neurons in layer 4\n\n";

    echo "5. Testing cleanup...\n";
    $deleted = GraphCriteria::node('Neuron')
        ->where('n.name', '=', 'Test Connection Neuron')
        ->delete();

    echo '✅ Deleted '.$deleted." test neurons\n\n";

    echo "🎉 All tests passed! Neo4j connection is working perfectly.\n";

} catch (Exception $e) {
    echo '❌ Connection failed: '.$e->getMessage()."\n";
    echo 'Error details: '.get_class($e)."\n";

    if ($e->getPrevious()) {
        echo 'Previous error: '.$e->getPrevious()->getMessage()."\n";
    }

    echo "\nTroubleshooting:\n";
    echo "1. Check if Neo4j is running: docker ps | grep neo4j\n";
    echo "2. Check if credentials are correct in .env file\n";
    echo "3. Check if Neo4j is accessible on the specified host:port\n";
    echo "4. Try connecting via Neo4j Browser at http://localhost:7474\n";
}
