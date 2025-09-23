<?php

namespace App\Services\CorticalNetwork;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DatabaseService
{
    private Criteria $criteria;

    public function __construct()
    {
        $this->criteria = new Criteria;
    }

    /**
     * Create a complete network snapshot including structure and state
     */
    public function createNetworkSnapshot(string $name, int $networkId, string $type = 'manual'): int
    {
        Log::info('Creating network snapshot', [
            'name' => $name,
            'network_id' => $networkId,
            'type' => $type,
        ]);

        // Verify network exists
        $network = Criteria::table('cortical_networks')
            ->where('id', $networkId)
            ->first();

        if (! $network) {
            throw new RuntimeException("Network not found: {$networkId}");
        }

        // Export network structure from Neo4j
        $networkData = $this->exportNetworkStructure($networkId);

        // Get current activation state
        $activationState = $this->getCurrentActivationState($networkId);

        // Create metadata
        $metadata = [
            'neuron_count' => count($networkData['neurons']),
            'connection_count' => count($networkData['relationships']),
            'active_neurons' => count(array_filter($activationState, fn ($n) => $n['activation_level'] > 0)),
            'snapshot_timestamp' => now()->toISOString(),
            'source_network' => $network->name,
        ];

        // Calculate data size
        $serializedData = serialize($networkData);
        $dataSize = strlen($serializedData);
        $checksum = md5($serializedData);

        // Save snapshot to database
        $snapshotData = [
            'cortical_network_id' => $networkId,
            'name' => $name,
            'type' => $type,
            'network_data' => base64_encode($serializedData),
            'activation_state' => json_encode($activationState),
            'metadata' => json_encode($metadata),
            'file_size' => $dataSize,
            'checksum' => $checksum,
            'created_by' => 'system',
        ];

        $snapshotId = Criteria::create('network_snapshots', $snapshotData);

        Log::info('Network snapshot created', [
            'snapshot_id' => $snapshotId,
            'name' => $name,
            'data_size_bytes' => $dataSize,
            'metadata' => $metadata,
        ]);

        return $snapshotId;
    }

    /**
     * Load and restore network from snapshot
     */
    public function loadNetworkSnapshot(string $name, int $networkId): array
    {
        Log::info('Loading network snapshot', [
            'name' => $name,
            'network_id' => $networkId,
        ]);

        // Get snapshot from database
        $snapshot = Criteria::table('network_snapshots')
            ->where('cortical_network_id', $networkId)
            ->where('name', $name)
            ->first();

        if (! $snapshot) {
            throw new RuntimeException("Snapshot not found: {$name} for network {$networkId}");
        }

        // Verify data integrity
        $serializedData = base64_decode($snapshot->network_data);
        $currentChecksum = md5($serializedData);

        if ($currentChecksum !== $snapshot->checksum) {
            throw new RuntimeException("Snapshot data integrity check failed for: {$name}");
        }

        // Deserialize network data
        $networkData = unserialize($serializedData);
        $activationState = json_decode($snapshot->activation_state, true);
        $metadata = json_decode($snapshot->metadata, true);

        // Clear current network in Neo4j
        $this->clearNetwork($networkId);

        // Restore network structure
        $restoredNetwork = $this->importNetworkStructure($networkData, $networkId);

        // Restore activation state
        $this->restoreActivationState($activationState);

        Log::info('Network snapshot loaded successfully', [
            'snapshot_id' => $snapshot->id,
            'neurons_restored' => count($networkData['neurons']),
            'connections_restored' => count($networkData['relationships']),
            'activations_restored' => count($activationState),
        ]);

        return [
            'snapshot' => $snapshot,
            'network_data' => $restoredNetwork,
            'metadata' => $metadata,
            'restored_at' => now(),
        ];
    }

    /**
     * Sync network metadata between Neo4j and CCF database
     */
    public function syncNetworkMetadata(): array
    {
        Log::info('Starting network metadata sync');

        $networks = Criteria::table('cortical_networks')
            ->where('status', 'active')
            ->get();

        $syncResults = [];

        foreach ($networks as $network) {
            try {
                // Get current statistics from Neo4j
                $neuronCount = GraphCriteria::node('Neuron')
                    ->where('n.column_id', '=', $network->id)
                    ->count();

                $connectionCount = GraphCriteria::match('(n:Neuron)-[r]-(m:Neuron)')
                    ->where('n.column_id', '=', $network->id)
                    ->returnClause('count(r) as connections')
                    ->first();

                $activeNeurons = GraphCriteria::node('Neuron')
                    ->where('n.column_id', '=', $network->id)
                    ->where('n.activation_level', '>', 0)
                    ->count();

                // Update CCF database
                Criteria::table('cortical_networks')
                    ->where('id', $network->id)
                    ->update([
                        'neuron_count' => $neuronCount,
                        'connection_count' => $connectionCount ? $connectionCount->connections : 0,
                    ]);

                // Update metadata table
                $this->updateMetadata($network->id, [
                    'neuron_count' => ['integer', $neuronCount],
                    'connection_count' => ['integer', $connectionCount ? $connectionCount->connections : 0],
                    'active_neurons' => ['integer', $activeNeurons],
                    'last_sync' => ['string', now()->toISOString()],
                ]);

                $syncResults[] = [
                    'network_id' => $network->id,
                    'name' => $network->name,
                    'status' => 'success',
                    'neuron_count' => $neuronCount,
                    'connection_count' => $connectionCount ? $connectionCount->connections : 0,
                ];

            } catch (\Exception $e) {
                Log::error("Failed to sync metadata for network {$network->id}", [
                    'error' => $e->getMessage(),
                ]);

                $syncResults[] = [
                    'network_id' => $network->id,
                    'name' => $network->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        Log::info('Network metadata sync completed', [
            'networks_processed' => count($syncResults),
            'successful' => count(array_filter($syncResults, fn ($r) => $r['status'] === 'success')),
        ]);

        return $syncResults;
    }

    /**
     * Export network data in various formats
     */
    public function exportNetwork(int $networkId, string $format = 'json'): string
    {
        $networkData = $this->exportNetworkStructure($networkId);
        $activationState = $this->getCurrentActivationState($networkId);

        $exportData = [
            'network_id' => $networkId,
            'exported_at' => now()->toISOString(),
            'structure' => $networkData,
            'activation_state' => $activationState,
        ];

        switch (strtolower($format)) {
            case 'json':
                return json_encode($exportData, JSON_PRETTY_PRINT);

            case 'cypher':
                return $this->generateCypherScript($networkData);

            case 'gexf':
                return $this->generateGexfFormat($networkData);

            default:
                throw new RuntimeException("Unsupported export format: {$format}");
        }
    }

    /**
     * Import network from external data
     */
    public function importNetwork(array $data, int $targetNetworkId): array
    {
        Log::info('Importing network data', [
            'target_network_id' => $targetNetworkId,
            'data_keys' => array_keys($data),
        ]);

        // Validate data structure
        $this->validateImportData($data);

        // Clear existing network
        $this->clearNetwork($targetNetworkId);

        // Import structure
        $importResult = $this->importNetworkStructure($data['structure'], $targetNetworkId);

        // Import activation state if present
        if (isset($data['activation_state'])) {
            $this->restoreActivationState($data['activation_state']);
        }

        // Sync metadata
        $this->syncNetworkMetadata();

        Log::info('Network import completed', [
            'target_network_id' => $targetNetworkId,
            'neurons_imported' => count($data['structure']['neurons']),
            'connections_imported' => count($data['structure']['relationships']),
        ]);

        return $importResult;
    }

    /**
     * Validate data consistency between Neo4j and CCF database
     */
    public function validateDataConsistency(): array
    {
        Log::info('Starting data consistency validation');

        $issues = [];
        $networks = Criteria::table('cortical_networks')->get();

        foreach ($networks as $network) {
            // Check if Neo4j neurons match metadata
            $actualNeuronCount = GraphCriteria::node('Neuron')
                ->where('n.column_id', '=', $network->id)
                ->count();

            if ($actualNeuronCount !== $network->neuron_count) {
                $issues[] = [
                    'type' => 'neuron_count_mismatch',
                    'network_id' => $network->id,
                    'metadata_count' => $network->neuron_count,
                    'actual_count' => $actualNeuronCount,
                ];
            }

            // Check for orphaned neurons
            $orphanedNeurons = GraphCriteria::node('Neuron')
                ->where('n.column_id', '=', $network->id)
                ->where('n.column_id IS NULL')
                ->count();

            if ($orphanedNeurons > 0) {
                $issues[] = [
                    'type' => 'orphaned_neurons',
                    'network_id' => $network->id,
                    'count' => $orphanedNeurons,
                ];
            }
        }

        return [
            'valid' => empty($issues),
            'issues_found' => count($issues),
            'issues' => $issues,
            'checked_at' => now(),
        ];
    }

    /**
     * Export network structure from Neo4j
     */
    private function exportNetworkStructure(int $networkId): array
    {
        // Get all neurons for this network
        $neurons = GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->returnClause('n, ID(n) as node_id')
            ->get();

        // Get all relationships between these neurons
        $relationships = GraphCriteria::match('(n:Neuron)-[r]-(m:Neuron)')
            ->where('n.column_id', '=', $networkId)
            ->returnClause('r, ID(startNode(r)) as start_id, ID(endNode(r)) as end_id')
            ->get();

        return [
            'neurons' => $neurons->toArray(),
            'relationships' => $relationships->toArray(),
        ];
    }

    /**
     * Get current activation state for all neurons in network
     */
    private function getCurrentActivationState(int $networkId): array
    {
        $activeNeurons = GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->where('n.activation_level', '>', 0)
            ->returnClause('ID(n) as neuron_id, n.activation_level, n.name')
            ->get();

        $state = [];
        foreach ($activeNeurons as $neuron) {
            $state[] = [
                'neuron_id' => $neuron->neuron_id,
                'name' => $neuron->name,
                'activation_level' => $neuron->activation_level,
            ];
        }

        return $state;
    }

    /**
     * Clear all neurons and relationships for a network
     */
    private function clearNetwork(int $networkId): void
    {
        // Delete relationships first
        GraphCriteria::match('(n:Neuron)-[r]-(m:Neuron)')
            ->where('n.column_id', '=', $networkId)
            ->getClient()
            ->run('MATCH (n:Neuron)-[r]-(m:Neuron) WHERE n.column_id = $networkId DELETE r', ['networkId' => $networkId]);

        // Delete neurons
        GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->delete();

        Log::info('Cleared network data', ['network_id' => $networkId]);
    }

    /**
     * Import network structure into Neo4j
     */
    private function importNetworkStructure(array $networkData, int $networkId): array
    {
        $nodeMapping = []; // Map old IDs to new IDs

        // Import neurons
        foreach ($networkData['neurons'] as $neuronData) {
            $properties = $neuronData['n']['properties'];
            $properties['column_id'] = $networkId; // Assign to target network

            $newNeuron = GraphCriteria::createNode('Neuron', $properties);
            if ($newNeuron) {
                $nodeMapping[$neuronData['node_id']] = $newNeuron->id;
            }
        }

        // Import relationships
        $relationshipCount = 0;
        foreach ($networkData['relationships'] as $relData) {
            $oldStartId = $relData['start_id'];
            $oldEndId = $relData['end_id'];

            if (isset($nodeMapping[$oldStartId]) && isset($nodeMapping[$oldEndId])) {
                $newStartId = $nodeMapping[$oldStartId];
                $newEndId = $nodeMapping[$oldEndId];

                $relationship = GraphCriteria::createRelation(
                    $newStartId,
                    $newEndId,
                    $relData['r']['type'],
                    $relData['r']['properties']
                );

                if ($relationship) {
                    $relationshipCount++;
                }
            }
        }

        return [
            'neurons_imported' => count($nodeMapping),
            'relationships_imported' => $relationshipCount,
            'node_mapping' => $nodeMapping,
        ];
    }

    /**
     * Restore activation state to neurons
     */
    private function restoreActivationState(array $activationState): void
    {
        foreach ($activationState as $state) {
            GraphCriteria::node('Neuron')
                ->where('ID(n)', '=', $state['neuron_id'])
                ->update(['activation_level' => $state['activation_level']]);
        }
    }

    /**
     * Update metadata for a network
     */
    private function updateMetadata(int $networkId, array $metadata): void
    {
        foreach ($metadata as $key => [$type, $value]) {
            DB::connection('ccf')->table('cortical_metadata')->updateOrInsert(
                [
                    'cortical_network_id' => $networkId,
                    'key' => $key,
                ],
                [
                    'value_type' => $type,
                    'value' => (string) $value,
                    'category' => 'system',
                    'is_system' => true,
                    'calculated_at' => now(),
                ]
            );
        }
    }

    /**
     * Validate import data structure
     */
    private function validateImportData(array $data): void
    {
        $requiredKeys = ['structure'];
        foreach ($requiredKeys as $key) {
            if (! isset($data[$key])) {
                throw new RuntimeException("Missing required import data key: {$key}");
            }
        }

        if (! isset($data['structure']['neurons']) || ! isset($data['structure']['relationships'])) {
            throw new RuntimeException('Invalid structure data: missing neurons or relationships');
        }
    }

    /**
     * Generate Cypher script for network recreation
     */
    private function generateCypherScript(array $networkData): string
    {
        $script = "// Generated Cypher script for network recreation\n\n";

        // Create neurons
        $script .= "// Create neurons\n";
        foreach ($networkData['neurons'] as $neuron) {
            $properties = $neuron['n']['properties'];
            $propertiesStr = json_encode($properties);
            $script .= "CREATE (n{$neuron['node_id']}:Neuron {$propertiesStr})\n";
        }

        $script .= "\n// Create relationships\n";
        foreach ($networkData['relationships'] as $rel) {
            $type = $rel['r']['type'];
            $properties = json_encode($rel['r']['properties']);
            $script .= "MATCH (a), (b) WHERE ID(a) = {$rel['start_id']} AND ID(b) = {$rel['end_id']} ";
            $script .= "CREATE (a)-[:{$type} {$properties}]->(b)\n";
        }

        return $script;
    }

    /**
     * Generate GEXF format for network visualization
     */
    private function generateGexfFormat(array $networkData): string
    {
        // Simplified GEXF generation - in a full implementation,
        // this would generate proper GEXF XML
        return json_encode([
            'format' => 'gexf',
            'nodes' => count($networkData['neurons']),
            'edges' => count($networkData['relationships']),
            'note' => 'Full GEXF implementation pending',
        ], JSON_PRETTY_PRINT);
    }
}
