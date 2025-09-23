<?php

namespace App\Services\CorticalNetwork;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NetworkService
{
    public function __construct()
    {
        // Service uses static Criteria calls
    }

    /**
     * Create a complete cortical column with 3-layer structure
     * Layer 4: Input, Layers 2/3: Processing, Layer 5: Output
     */
    public function createCorticalColumn(array $config): array
    {
        $columnName = $config['name'] ?? 'Cortical Column';
        $layerConfig = $config['layer_config'] ?? $this->getDefaultLayerConfig();

        // Save network definition to CCF database
        $networkId = $this->saveNetworkDefinition($columnName, $config);

        $neurons = [];

        // Create Layer 4 neurons (Input)
        for ($i = 0; $i < $layerConfig['layer_4']['count']; $i++) {
            $neuron = GraphCriteria::createNode('Neuron', [
                'name' => "{$columnName}_L4_{$i}",
                'layer' => 4,
                'column_id' => $networkId,
                'activation_level' => $layerConfig['layer_4']['activation_level'] ?? 0.0,
                'threshold' => $layerConfig['layer_4']['threshold'] ?? 0.5,
                'neuron_type' => 'input',
            ]);

            if ($neuron) {
                $neurons['layer_4'][] = $neuron;
            }
        }

        // Create Layers 2/3 neurons (Processing)
        for ($i = 0; $i < $layerConfig['layer_23']['count']; $i++) {
            $neuron = GraphCriteria::createNode('Neuron', [
                'name' => "{$columnName}_L23_{$i}",
                'layer' => 23,
                'column_id' => $networkId,
                'activation_level' => $layerConfig['layer_23']['activation_level'] ?? 0.0,
                'threshold' => $layerConfig['layer_23']['threshold'] ?? 0.5,
                'neuron_type' => 'processing',
            ]);

            if ($neuron) {
                $neurons['layer_23'][] = $neuron;
            }
        }

        // Create Layer 5 neurons (Output)
        for ($i = 0; $i < $layerConfig['layer_5']['count']; $i++) {
            $neuron = GraphCriteria::createNode('Neuron', [
                'name' => "{$columnName}_L5_{$i}",
                'layer' => 5,
                'column_id' => $networkId,
                'activation_level' => $layerConfig['layer_5']['activation_level'] ?? 0.0,
                'threshold' => $layerConfig['layer_5']['threshold'] ?? 0.5,
                'neuron_type' => 'output',
            ]);

            if ($neuron) {
                $neurons['layer_5'][] = $neuron;
            }
        }

        // Create intra-column connections
        $this->createIntraColumnConnections($neurons, $layerConfig);

        // Update network metadata
        $this->updateNetworkMetadata($networkId, $neurons);

        Log::info('Created cortical column', [
            'network_id' => $networkId,
            'name' => $columnName,
            'neuron_count' => count($neurons, COUNT_RECURSIVE) - count($neurons),
        ]);

        return [
            'network_id' => $networkId,
            'neurons' => $neurons,
            'configuration' => $config,
        ];
    }

    /**
     * Create individual neuron with specified properties
     */
    public function createNeuron(string $name, int $layer, array $properties = []): ?object
    {
        $properties = array_merge([
            'name' => $name,
            'layer' => $layer,
            'activation_level' => config('neo4j.cortical_network.default_properties.activation_level', 0.0),
            'threshold' => config('neo4j.cortical_network.default_properties.threshold', 0.5),
        ], $properties);

        return GraphCriteria::createNode('Neuron', $properties);
    }

    /**
     * Connect two neurons with specified relationship type
     */
    public function connectNeurons(int $fromId, int $toId, string $type = 'CONNECTS_TO', array $properties = []): ?object
    {
        $validTypes = ['CONNECTS_TO', 'ACTIVATES', 'INHIBITS'];

        if (! in_array($type, $validTypes)) {
            throw new RuntimeException("Invalid connection type: {$type}. Valid types: ".implode(', ', $validTypes));
        }

        $properties = array_merge([
            'weight' => 0.5,
            'strength' => 0.5,
        ], $properties);

        return GraphCriteria::createRelation($fromId, $toId, $type, $properties);
    }

    /**
     * Get complete cortical column structure
     */
    public function getCorticalColumn(int $networkId): array
    {
        // Get network definition from CCF database
        $network = Criteria::table('cortical_networks')
            ->where('id', $networkId)
            ->first();

        if (! $network) {
            throw new RuntimeException("Network not found: {$networkId}");
        }

        // Get neurons from Neo4j
        $neurons = GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->returnClause('n, ID(n) as node_id')
            ->orderBy('n.layer', 'ASC')
            ->get();

        // Group neurons by layer
        $groupedNeurons = [];
        foreach ($neurons as $neuron) {
            $layer = $neuron->n->properties->layer;
            $layerKey = $layer == 23 ? 'layer_23' : "layer_{$layer}";
            $groupedNeurons[$layerKey][] = $neuron;
        }

        return [
            'network' => $network,
            'neurons' => $groupedNeurons,
            'configuration' => json_decode($network->configuration, true),
        ];
    }

    /**
     * Get network structure with optional filters
     */
    public function getNetworkStructure(array $filters = []): Collection
    {
        $query = GraphCriteria::node('Neuron');

        if (isset($filters['layer'])) {
            $query->where('n.layer', '=', $filters['layer']);
        }

        if (isset($filters['column_id'])) {
            $query->where('n.column_id', '=', $filters['column_id']);
        }

        if (isset($filters['neuron_type'])) {
            $query->where('n.neuron_type', '=', $filters['neuron_type']);
        }

        return $query
            ->returnClause('n, ID(n) as node_id')
            ->orderBy('n.layer', 'ASC')
            ->limit($filters['limit'] ?? 100)
            ->get();
    }

    /**
     * Save network definition to CCF database
     */
    public function saveNetworkDefinition(string $name, array $config): int
    {
        $data = [
            'name' => $name,
            'description' => $config['description'] ?? "Cortical network: {$name}",
            'configuration' => json_encode($config),
            'layer_config' => json_encode($config['layer_config'] ?? $this->getDefaultLayerConfig()),
            'status' => 'active',
            'created_by' => $config['created_by'] ?? 'system',
        ];

        return Criteria::table('cortical_networks')->insertGetId($data);
    }

    /**
     * Load network definition from CCF database
     */
    public function loadNetworkDefinition(string $name): ?array
    {
        $network = Criteria::table('cortical_networks')
            ->where('name', $name)
            ->first();

        if (! $network) {
            return null;
        }

        return [
            'id' => $network->id,
            'name' => $network->name,
            'description' => $network->description,
            'configuration' => json_decode($network->configuration, true),
            'layer_config' => json_decode($network->layer_config, true),
            'status' => $network->status,
            'metadata' => [
                'neuron_count' => $network->neuron_count,
                'connection_count' => $network->connection_count,
                'last_activation' => $network->last_activation,
                'created_at' => $network->created_at,
            ],
        ];
    }

    /**
     * Validate network integrity
     */
    public function validateNetworkIntegrity(): array
    {
        $issues = [];

        // Check for orphaned neurons
        $orphanedNeurons = GraphCriteria::node('Neuron')
            ->where('n.column_id IS NULL')
            ->count();

        if ($orphanedNeurons > 0) {
            $issues[] = "Found {$orphanedNeurons} orphaned neurons without column assignment";
        }

        // Check for missing layer assignments
        $invalidLayers = GraphCriteria::node('Neuron')
            ->where('n.layer NOT IN [4, 23, 5]')
            ->count();

        if ($invalidLayers > 0) {
            $issues[] = "Found {$invalidLayers} neurons with invalid layer assignments";
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'timestamp' => now(),
        ];
    }

    /**
     * Get default layer configuration
     */
    private function getDefaultLayerConfig(): array
    {
        return [
            'layer_4' => [
                'count' => 10,
                'activation_level' => 0.0,
                'threshold' => 0.5,
                'description' => 'Input layer - receives external stimuli',
            ],
            'layer_23' => [
                'count' => 20,
                'activation_level' => 0.0,
                'threshold' => 0.6,
                'description' => 'Processing layers - feature detection and frame elements',
            ],
            'layer_5' => [
                'count' => 8,
                'activation_level' => 0.0,
                'threshold' => 0.7,
                'description' => 'Output layer - motor output and concept broadcasting',
            ],
        ];
    }

    /**
     * Create standard intra-column connections
     */
    private function createIntraColumnConnections(array $neurons, array $layerConfig): void
    {
        // Connect Layer 4 to Layers 2/3 (feedforward)
        if (isset($neurons['layer_4']) && isset($neurons['layer_23'])) {
            foreach ($neurons['layer_4'] as $l4Neuron) {
                foreach ($neurons['layer_23'] as $l23Neuron) {
                    if (rand(1, 100) <= 60) { // 60% connection probability
                        $this->connectNeurons($l4Neuron->id, $l23Neuron->id, 'ACTIVATES', [
                            'weight' => rand(3, 8) / 10,
                            'connection_type' => 'feedforward',
                        ]);
                    }
                }
            }
        }

        // Connect Layers 2/3 to Layer 5 (feedforward)
        if (isset($neurons['layer_23']) && isset($neurons['layer_5'])) {
            foreach ($neurons['layer_23'] as $l23Neuron) {
                foreach ($neurons['layer_5'] as $l5Neuron) {
                    if (rand(1, 100) <= 40) { // 40% connection probability
                        $this->connectNeurons($l23Neuron->id, $l5Neuron->id, 'ACTIVATES', [
                            'weight' => rand(4, 9) / 10,
                            'connection_type' => 'feedforward',
                        ]);
                    }
                }
            }
        }

        // Create some lateral connections within layers
        $this->createLateralConnections($neurons);
    }

    /**
     * Create lateral connections within layers
     */
    private function createLateralConnections(array $neurons): void
    {
        foreach (['layer_23'] as $layer) {
            if (! isset($neurons[$layer])) {
                continue;
            }

            $layerNeurons = $neurons[$layer];
            for ($i = 0; $i < count($layerNeurons); $i++) {
                for ($j = $i + 1; $j < count($layerNeurons); $j++) {
                    if (rand(1, 100) <= 15) { // 15% lateral connection probability
                        $connectionType = rand(1, 100) <= 70 ? 'ACTIVATES' : 'INHIBITS';
                        $this->connectNeurons($layerNeurons[$i]->id, $layerNeurons[$j]->id, $connectionType, [
                            'weight' => rand(2, 6) / 10,
                            'connection_type' => 'lateral',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Update network metadata in CCF database
     */
    private function updateNetworkMetadata(int $networkId, array $neurons): void
    {
        $neuronCount = count($neurons, COUNT_RECURSIVE) - count($neurons);

        Criteria::table('cortical_networks')
            ->where('id', $networkId)
            ->update([
                'neuron_count' => $neuronCount,
                'last_activation' => now(),
            ]);
    }
}
