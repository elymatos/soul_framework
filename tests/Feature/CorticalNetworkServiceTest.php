<?php

namespace Tests\Feature;

use App\Database\GraphCriteria;
use App\Services\CorticalNetwork\ActivationService;
use App\Services\CorticalNetwork\DatabaseService;
use App\Services\CorticalNetwork\NetworkService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CorticalNetworkServiceTest extends TestCase
{
    private NetworkService $networkService;

    private ActivationService $activationService;

    private DatabaseService $databaseService;

    private string $testTimestamp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testTimestamp = (string) now()->timestamp;

        $this->networkService = new NetworkService;
        $this->activationService = new ActivationService;
        $this->databaseService = new DatabaseService;

        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    public function test_can_create_cortical_column(): void
    {
        $config = [
            'name' => "Test Cortical Column {$this->testTimestamp}",
            'description' => 'Test column for Phase 1.2',
            'layer_config' => [
                'layer_4' => ['count' => 3, 'activation_level' => 0.0, 'threshold' => 0.5],
                'layer_23' => ['count' => 5, 'activation_level' => 0.0, 'threshold' => 0.6],
                'layer_5' => ['count' => 2, 'activation_level' => 0.0, 'threshold' => 0.7],
            ],
        ];

        $result = $this->networkService->createCorticalColumn($config);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('network_id', $result);
        $this->assertArrayHasKey('neurons', $result);
        $this->assertArrayHasKey('configuration', $result);

        // Verify neuron counts per layer
        $this->assertCount(3, $result['neurons']['layer_4']);
        $this->assertCount(5, $result['neurons']['layer_23']);
        $this->assertCount(2, $result['neurons']['layer_5']);
    }

    public function test_can_create_individual_neuron(): void
    {
        $neuron = $this->networkService->createNeuron('Test Neuron', 4, [
            'activation_level' => 0.3,
            'threshold' => 0.8,
        ]);

        $this->assertNotNull($neuron);
        $this->assertEquals('Test Neuron', $neuron->properties['name']);
        $this->assertEquals(4, $neuron->properties['layer']);
        $this->assertEquals(0.3, $neuron->properties['activation_level']);
        $this->assertEquals(0.8, $neuron->properties['threshold']);
    }

    public function test_can_connect_neurons(): void
    {
        // Create two neurons
        $neuron1 = $this->networkService->createNeuron('Neuron 1', 4);
        $neuron2 = $this->networkService->createNeuron('Neuron 2', 23);

        $this->assertNotNull($neuron1);
        $this->assertNotNull($neuron2);

        // Connect them
        $connection = $this->networkService->connectNeurons(
            $neuron1->id,
            $neuron2->id,
            'ACTIVATES',
            ['weight' => 0.7, 'strength' => 0.9]
        );

        $this->assertNotNull($connection);
        $this->assertEquals('ACTIVATES', $connection->type);
        $this->assertEquals($neuron1->id, $connection->start_node_id);
        $this->assertEquals($neuron2->id, $connection->end_node_id);
    }

    public function test_can_activate_neuron(): void
    {
        $neuron = $this->networkService->createNeuron('Test Neuron', 4);
        $this->assertNotNull($neuron);

        $success = $this->activationService->activateNeuron($neuron->id, 0.8);
        $this->assertTrue($success);

        // Verify activation was set using direct Cypher query
        $criteria = GraphCriteria::node('Neuron');
        $result = $criteria->getClient()->run(
            'MATCH (n:Neuron) WHERE id(n) = $id RETURN n',
            ['id' => $neuron->id]
        );

        $this->assertEquals(1, $result->count());
        $activatedNode = $result->first()->get('n');
        $this->assertEquals(0.8, $activatedNode->getProperties()['activation_level']);
    }

    public function test_can_start_and_complete_activation_session(): void
    {
        // Create a test network
        $config = [
            'name' => "Session Test Network {$this->testTimestamp}",
            'layer_config' => [
                'layer_4' => ['count' => 2],
                'layer_23' => ['count' => 2],
                'layer_5' => ['count' => 1],
            ],
        ];

        $column = $this->networkService->createCorticalColumn($config);
        $networkId = $column['network_id'];

        // Start session
        $sessionId = $this->activationService->startActivationSession(
            'Test Session',
            $networkId,
            ['description' => 'Testing session functionality']
        );

        $this->assertIsInt($sessionId);
        $this->assertGreaterThan(0, $sessionId);

        // Complete session
        $this->activationService->completeActivationSession([
            'test_metric' => 'completed',
        ]);

        // Session should be completed
        $this->assertTrue(true); // Session completed without exception
    }

    public function test_can_perform_spread_activation(): void
    {
        // Create a small network
        $neuron1 = $this->networkService->createNeuron('Source', 4);
        $neuron2 = $this->networkService->createNeuron('Target 1', 23);
        $neuron3 = $this->networkService->createNeuron('Target 2', 23);

        // Connect them
        $this->networkService->connectNeurons($neuron1->id, $neuron2->id, 'ACTIVATES', ['weight' => 0.8]);
        $this->networkService->connectNeurons($neuron1->id, $neuron3->id, 'ACTIVATES', ['weight' => 0.6]);

        // Activate source neuron
        $this->activationService->activateNeuron($neuron1->id, 1.0);

        // Perform spread activation
        $result = $this->activationService->spreadActivation($neuron1->id, [
            'max_steps' => 3,
            'decay_factor' => 0.8,
            'threshold' => 0.1,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('source_id', $result);
        $this->assertArrayHasKey('total_steps', $result);
        $this->assertArrayHasKey('activation_history', $result);
        $this->assertEquals($neuron1->id, $result['source_id']);
    }

    public function test_can_create_and_load_network_snapshot(): void
    {
        // Create a test network with some activation
        $config = ['name' => "Snapshot Test Network {$this->testTimestamp}"];
        $column = $this->networkService->createCorticalColumn($config);
        $networkId = $column['network_id'];

        // Activate some neurons
        $neurons = $column['neurons']['layer_4'];
        if (! empty($neurons)) {
            $this->activationService->activateNeuron($neurons[0]->id, 0.7);
        }

        // Create snapshot
        $snapshotId = $this->databaseService->createNetworkSnapshot(
            'Test Snapshot',
            $networkId,
            'test'
        );

        $this->assertIsInt($snapshotId);
        $this->assertGreaterThan(0, $snapshotId);

        // Clear network and reload from snapshot
        $loadResult = $this->databaseService->loadNetworkSnapshot('Test Snapshot', $networkId);

        $this->assertIsArray($loadResult);
        $this->assertArrayHasKey('snapshot', $loadResult);
        $this->assertArrayHasKey('network_data', $loadResult);
    }

    public function test_can_sync_network_metadata(): void
    {
        // Create a test network
        $config = ['name' => "Metadata Test Network {$this->testTimestamp}"];
        $column = $this->networkService->createCorticalColumn($config);

        // Sync metadata
        $syncResults = $this->databaseService->syncNetworkMetadata();

        $this->assertIsArray($syncResults);
        $this->assertNotEmpty($syncResults);

        // Find our test network in results
        $testNetworkResult = collect($syncResults)->firstWhere('network_id', $column['network_id']);
        $this->assertNotNull($testNetworkResult);
        $this->assertEquals('success', $testNetworkResult['status']);
    }

    public function test_can_validate_network_integrity(): void
    {
        $validation = $this->networkService->validateNetworkIntegrity();

        $this->assertIsArray($validation);
        $this->assertArrayHasKey('valid', $validation);
        $this->assertArrayHasKey('issues', $validation);
        $this->assertArrayHasKey('timestamp', $validation);
        $this->assertIsBool($validation['valid']);
    }

    public function test_can_export_network(): void
    {
        // Create a small test network
        $config = ['name' => "Export Test Network {$this->testTimestamp}"];
        $column = $this->networkService->createCorticalColumn($config);
        $networkId = $column['network_id'];

        // Export as JSON
        $jsonExport = $this->databaseService->exportNetwork($networkId, 'json');
        $this->assertIsString($jsonExport);

        $exportData = json_decode($jsonExport, true);
        $this->assertIsArray($exportData);
        $this->assertArrayHasKey('network_id', $exportData);
        $this->assertArrayHasKey('structure', $exportData);

        // Export as Cypher
        $cypherExport = $this->databaseService->exportNetwork($networkId, 'cypher');
        $this->assertIsString($cypherExport);
        $this->assertStringContainsString('// Generated Cypher script', $cypherExport);
    }

    private function cleanupTestData(): void
    {
        try {
            // Clean up MariaDB test networks
            DB::connection('ccf')->table('cortical_networks')
                ->where('name', 'like', '%Test%')
                ->delete();

            DB::connection('ccf')->table('activation_sessions')
                ->where('session_name', 'like', '%Test%')
                ->delete();

            DB::connection('ccf')->table('network_snapshots')
                ->where('snapshot_name', 'like', '%Test%')
                ->delete();

            // Clean up Neo4j test neurons
            GraphCriteria::node('Neuron')
                ->getClient()
                ->run('MATCH (n:Neuron) WHERE n.name CONTAINS "Test" DETACH DELETE n');
        } catch (\Exception $e) {
            // Ignore cleanup errors during setup
        }
    }
}
