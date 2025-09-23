<?php

namespace Tests\Unit\Repositories;

use App\Database\GraphCriteria;
use App\Repositories\ActivationStateRepository;
use App\Repositories\ColumnConnectionRepository;
use App\Repositories\CorticalColumnRepository;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CorticalRepositoriesTest extends TestCase
{
    private CorticalColumnRepository $columnRepo;

    private ColumnConnectionRepository $connectionRepo;

    private ActivationStateRepository $activationRepo;

    private string $testTimestamp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testTimestamp = (string) now()->timestamp;
        $this->columnRepo = new CorticalColumnRepository;
        $this->connectionRepo = new ColumnConnectionRepository;
        $this->activationRepo = new ActivationStateRepository;

        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        parent::tearDown();
    }

    public function test_can_create_cortical_column(): void
    {
        $data = [
            'name' => "Test Column {$this->testTimestamp}",
            'description' => 'Test column for repository',
            'layer_config' => [
                'layer_4' => ['count' => 3],
                'layer_23' => ['count' => 5],
                'layer_5' => ['count' => 2],
            ],
        ];

        $columnId = $this->columnRepo->create($data);

        $this->assertIsInt($columnId);
        $this->assertGreaterThan(0, $columnId);

        $column = $this->columnRepo->find($columnId);
        $this->assertNotNull($column);
        $this->assertEquals($data['name'], $column->name);
        $this->assertEquals('active', $column->status);
    }

    public function test_can_find_column_by_name(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Findable Column {$this->testTimestamp}",
        ]);

        $column = $this->columnRepo->findByName("Findable Column {$this->testTimestamp}");

        $this->assertNotNull($column);
        $this->assertEquals($columnId, $column->id);
    }

    public function test_can_update_column(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Updatable Column {$this->testTimestamp}",
        ]);

        $updated = $this->columnRepo->update($columnId, [
            'description' => 'Updated description',
            'status' => 'inactive',
        ]);

        $this->assertTrue($updated);

        $column = $this->columnRepo->find($columnId);
        $this->assertEquals('Updated description', $column->description);
        $this->assertEquals('inactive', $column->status);
    }

    public function test_can_archive_column(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Archivable Column {$this->testTimestamp}",
        ]);

        $archived = $this->columnRepo->archive($columnId);

        $this->assertTrue($archived);

        $column = $this->columnRepo->find($columnId);
        $this->assertEquals('archived', $column->status);
    }

    public function test_can_get_columns_by_status(): void
    {
        $this->columnRepo->create([
            'name' => "Active Test {$this->testTimestamp}",
            'status' => 'active',
        ]);

        $activeColumns = $this->columnRepo->getByStatus('active');

        $this->assertGreaterThan(0, $activeColumns->count());
        $this->assertTrue($activeColumns->contains(fn ($c) => str_contains($c->name, $this->testTimestamp)));
    }

    public function test_can_create_connection(): void
    {
        // Create two neurons
        $neuron1 = GraphCriteria::createNode('Neuron', [
            'name' => 'Test Neuron 1',
            'layer' => 4,
        ]);

        $neuron2 = GraphCriteria::createNode('Neuron', [
            'name' => 'Test Neuron 2',
            'layer' => 23,
        ]);

        // Create connection
        $connection = $this->connectionRepo->createConnection(
            $neuron1->id,
            $neuron2->id,
            'ACTIVATES',
            ['weight' => 0.8]
        );

        $this->assertNotNull($connection);
        $this->assertEquals('ACTIVATES', $connection->type);
        $this->assertEquals(0.8, $connection->properties['weight']);
    }

    public function test_can_get_neuron_connections(): void
    {
        // Create neurons and connection
        $neuron1 = GraphCriteria::createNode('Neuron', ['name' => 'Source', 'layer' => 4]);
        $neuron2 = GraphCriteria::createNode('Neuron', ['name' => 'Target', 'layer' => 23]);

        $this->connectionRepo->createConnection($neuron1->id, $neuron2->id, 'ACTIVATES');

        // Get outgoing connections
        $outgoing = $this->connectionRepo->getConnectionsForNeuron($neuron1->id, 'outgoing');

        $this->assertGreaterThan(0, $outgoing->count());
        $this->assertEquals($neuron1->id, $outgoing->first()->start_node_id);
        $this->assertEquals($neuron2->id, $outgoing->first()->end_node_id);
    }

    public function test_can_update_connection_weight(): void
    {
        $neuron1 = GraphCriteria::createNode('Neuron', ['name' => 'N1', 'layer' => 4]);
        $neuron2 = GraphCriteria::createNode('Neuron', ['name' => 'N2', 'layer' => 23]);

        $this->connectionRepo->createConnection($neuron1->id, $neuron2->id, 'ACTIVATES', ['weight' => 0.5]);

        $updated = $this->connectionRepo->updateConnectionWeight($neuron1->id, $neuron2->id, 0.9);

        $this->assertTrue($updated);
    }

    public function test_can_delete_connection(): void
    {
        $neuron1 = GraphCriteria::createNode('Neuron', ['name' => 'Del1', 'layer' => 4]);
        $neuron2 = GraphCriteria::createNode('Neuron', ['name' => 'Del2', 'layer' => 23]);

        $this->connectionRepo->createConnection($neuron1->id, $neuron2->id);

        $deleted = $this->connectionRepo->deleteConnection($neuron1->id, $neuron2->id);

        $this->assertTrue($deleted);
    }

    public function test_can_set_and_get_activation(): void
    {
        $neuron = GraphCriteria::createNode('Neuron', [
            'name' => 'Activation Test',
            'layer' => 4,
            'activation_level' => 0.0,
        ]);

        $this->activationRepo->setActivation($neuron->id, 0.75);

        $level = $this->activationRepo->getActivation($neuron->id);

        $this->assertEquals(0.75, $level);
    }

    public function test_can_get_active_neurons(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Active Neurons Test {$this->testTimestamp}",
        ]);

        $neuron1 = GraphCriteria::createNode('Neuron', [
            'name' => 'Active 1',
            'layer' => 4,
            'column_id' => $columnId,
            'activation_level' => 0.8,
        ]);

        $neuron2 = GraphCriteria::createNode('Neuron', [
            'name' => 'Inactive',
            'layer' => 23,
            'column_id' => $columnId,
            'activation_level' => 0.0,
        ]);

        $activeNeurons = $this->activationRepo->getActiveNeurons($columnId, 0.5);

        $this->assertGreaterThan(0, $activeNeurons->count());
        $this->assertTrue($activeNeurons->contains(fn ($n) => $n->neuron_id == $neuron1->id));
    }

    public function test_can_reset_activations(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Reset Test {$this->testTimestamp}",
        ]);

        GraphCriteria::createNode('Neuron', [
            'name' => 'Reset1',
            'layer' => 4,
            'column_id' => $columnId,
            'activation_level' => 0.9,
        ]);

        $resetCount = $this->activationRepo->resetActivations($columnId);

        $this->assertGreaterThan(0, $resetCount);
    }

    public function test_can_get_activation_statistics(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Stats Test {$this->testTimestamp}",
        ]);

        GraphCriteria::createNode('Neuron', [
            'name' => 'Stat1',
            'layer' => 4,
            'column_id' => $columnId,
            'activation_level' => 0.5,
        ]);

        GraphCriteria::createNode('Neuron', [
            'name' => 'Stat2',
            'layer' => 23,
            'column_id' => $columnId,
            'activation_level' => 0.8,
        ]);

        $stats = $this->activationRepo->getActivationStatistics($columnId);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_neurons', $stats);
        $this->assertArrayHasKey('active_neurons', $stats);
        $this->assertGreaterThan(0, $stats['total_neurons']);
    }

    public function test_can_set_multiple_activations(): void
    {
        $neuron1 = GraphCriteria::createNode('Neuron', ['name' => 'Multi1', 'layer' => 4]);
        $neuron2 = GraphCriteria::createNode('Neuron', ['name' => 'Multi2', 'layer' => 23]);

        $updated = $this->activationRepo->setMultipleActivations([
            $neuron1->id => 0.6,
            $neuron2->id => 0.7,
        ]);

        $this->assertEquals(2, $updated);
    }

    public function test_can_get_column_statistics(): void
    {
        $columnId = $this->columnRepo->create([
            'name' => "Column Stats Test {$this->testTimestamp}",
        ]);

        GraphCriteria::createNode('Neuron', [
            'name' => 'CStat1',
            'layer' => 4,
            'column_id' => $columnId,
        ]);

        $stats = $this->columnRepo->getStatistics($columnId);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('neuron_count', $stats);
        $this->assertArrayHasKey('connection_count', $stats);
        $this->assertEquals($columnId, $stats['id']);
    }

    private function cleanupTestData(): void
    {
        try {
            // Clean up MariaDB
            DB::connection('ccf')->table('cortical_networks')
                ->where('name', 'like', '%Test%')
                ->delete();

            DB::connection('ccf')->table('activation_snapshots')
                ->where('neuron_count', '>', -1)
                ->delete();

            // Clean up Neo4j
            GraphCriteria::node('Neuron')
                ->getClient()
                ->run('MATCH (n:Neuron) WHERE n.name CONTAINS "Test" OR n.name CONTAINS "N1" OR n.name CONTAINS "N2" OR n.name CONTAINS "Del" OR n.name CONTAINS "Active" OR n.name CONTAINS "Reset" OR n.name CONTAINS "Stat" OR n.name CONTAINS "Multi" OR n.name CONTAINS "Source" OR n.name CONTAINS "Target" OR n.name CONTAINS "Inactive" OR n.name CONTAINS "Findable" OR n.name CONTAINS "Updatable" OR n.name CONTAINS "Archivable" DETACH DELETE n');
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
}
