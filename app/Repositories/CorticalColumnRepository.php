<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CorticalColumnRepository
{
    public function create(array $data): int
    {
        $columnName = $data['name'] ?? throw new RuntimeException('Column name is required');
        $layerConfig = $data['layer_config'] ?? $this->getDefaultLayerConfig();

        $networkData = [
            'name' => $columnName,
            'description' => $data['description'] ?? "Cortical network: {$columnName}",
            'configuration' => json_encode($data['configuration'] ?? ['name' => $columnName]),
            'layer_config' => json_encode($layerConfig),
            'status' => $data['status'] ?? 'active',
            'created_by' => $data['created_by'] ?? 'system',
        ];

        $networkId = Criteria::create('cortical_networks', $networkData);

        Log::info('Created cortical column', [
            'id' => $networkId,
            'name' => $columnName,
            'layers' => array_keys($layerConfig),
        ]);

        return $networkId;
    }

    public function find(int $id): ?object
    {
        return Criteria::table('cortical_networks')
            ->where('id', $id)
            ->first();
    }

    public function findByName(string $name): ?object
    {
        return Criteria::table('cortical_networks')
            ->where('name', $name)
            ->first();
    }

    public function update(int $id, array $data): bool
    {
        $updateData = [];

        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }

        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
        }

        if (isset($data['configuration'])) {
            $updateData['configuration'] = json_encode($data['configuration']);
        }

        if (isset($data['layer_config'])) {
            $updateData['layer_config'] = json_encode($data['layer_config']);
        }

        if (isset($data['performance_metrics'])) {
            $updateData['performance_metrics'] = json_encode($data['performance_metrics']);
        }

        if (empty($updateData)) {
            return false;
        }

        $result = Criteria::table('cortical_networks')
            ->where('id', $id)
            ->update($updateData);

        Log::info('Updated cortical column', [
            'id' => $id,
            'fields' => array_keys($updateData),
        ]);

        return $result > 0;
    }

    public function delete(int $id, bool $hard = false): bool
    {
        if ($hard) {
            // Hard delete: remove from database and Neo4j
            $this->deleteNeurons($id);

            $result = Criteria::table('cortical_networks')
                ->where('id', $id)
                ->delete();

            Log::info('Hard deleted cortical column', ['id' => $id]);

            return $result > 0;
        }

        // Soft delete: just set status to archived
        return $this->update($id, ['status' => 'archived']);
    }

    public function updateStatistics(int $id, array $stats): void
    {
        $updateData = [];

        if (isset($stats['neuron_count'])) {
            $updateData['neuron_count'] = $stats['neuron_count'];
        }

        if (isset($stats['connection_count'])) {
            $updateData['connection_count'] = $stats['connection_count'];
        }

        if (isset($stats['last_activation'])) {
            $updateData['last_activation'] = $stats['last_activation'];
        }

        if (! empty($updateData)) {
            Criteria::table('cortical_networks')
                ->where('id', $id)
                ->update($updateData);
        }
    }

    public function getActiveColumns(): Collection
    {
        return Criteria::table('cortical_networks')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByStatus(string $status): Collection
    {
        return Criteria::table('cortical_networks')
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAll(): Collection
    {
        return Criteria::table('cortical_networks')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getNeuronCount(int $networkId): int
    {
        return GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->count();
    }

    public function getLayerNeurons(int $networkId, int $layer): Collection
    {
        return GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->where('n.layer', '=', $layer)
            ->returnClause('n, id(n) as neuron_id')
            ->get();
    }

    public function getAllNeurons(int $networkId): array
    {
        return [
            'layer_4' => $this->getLayerNeurons($networkId, 4),
            'layer_23' => $this->getLayerNeurons($networkId, 23),
            'layer_5' => $this->getLayerNeurons($networkId, 5),
        ];
    }

    public function archive(int $id): bool
    {
        return $this->update($id, [
            'status' => 'archived',
            'performance_metrics' => [
                'archived_at' => now()->toISOString(),
            ],
        ]);
    }

    public function activate(int $id): bool
    {
        return $this->update($id, ['status' => 'active']);
    }

    public function deactivate(int $id): bool
    {
        return $this->update($id, ['status' => 'inactive']);
    }

    public function getStatistics(int $id): array
    {
        $network = $this->find($id);

        if (! $network) {
            throw new RuntimeException("Network not found: {$id}");
        }

        $neuronCount = $this->getNeuronCount($id);

        $connectionCount = GraphCriteria::match('(n:Neuron)-[r]->(m:Neuron)')
            ->where('n.column_id', '=', $id)
            ->returnClause('count(r) as connections')
            ->first();

        $activeNeurons = GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $id)
            ->where('n.activation_level', '>', 0)
            ->count();

        return [
            'id' => $id,
            'name' => $network->name,
            'status' => $network->status,
            'neuron_count' => $neuronCount,
            'connection_count' => $connectionCount?->connections ?? 0,
            'active_neurons' => $activeNeurons,
            'created_at' => $network->created_at,
            'last_activation' => $network->last_activation,
        ];
    }

    private function deleteNeurons(int $networkId): void
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
    }

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
}
