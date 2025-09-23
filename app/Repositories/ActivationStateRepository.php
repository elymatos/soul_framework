<?php

namespace App\Repositories;

use App\Database\Criteria;
use App\Database\GraphCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ActivationStateRepository
{
    public function setActivation(int $neuronId, float $level): bool
    {
        if ($level < 0.0 || $level > 1.0) {
            throw new RuntimeException("Activation level must be between 0.0 and 1.0, got: {$level}");
        }

        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE id(n) = $neuronId SET n.activation_level = $level RETURN n';

        $result = $client->run($query, [
            'neuronId' => $neuronId,
            'level' => $level,
        ]);

        Log::debug('Set neuron activation', [
            'neuron_id' => $neuronId,
            'level' => $level,
        ]);

        return $result->count() > 0;
    }

    public function getActivation(int $neuronId): float
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE id(n) = $neuronId RETURN n.activation_level as level';

        $result = $client->run($query, ['neuronId' => $neuronId]);

        if ($result->count() === 0) {
            throw new RuntimeException("Neuron not found: {$neuronId}");
        }

        return (float) ($result->first()->get('level') ?? 0.0);
    }

    public function getActiveNeurons(int $networkId, float $threshold = 0.0): Collection
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE n.column_id = $networkId AND n.activation_level > $threshold RETURN n, id(n) as neuron_id, n.activation_level as level';

        $result = $client->run($query, [
            'networkId' => $networkId,
            'threshold' => $threshold,
        ]);

        return collect($result)->map(function ($record) {
            return (object) [
                'n' => $record->get('n'),
                'neuron_id' => $record->get('neuron_id'),
                'level' => $record->get('level'),
            ];
        });
    }

    public function resetActivations(int $networkId): int
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE n.column_id = $networkId SET n.activation_level = 0.0 RETURN count(n) as reset_count';

        $result = $client->run($query, ['networkId' => $networkId]);

        $count = $result->first()?->get('reset_count') ?? 0;

        Log::info('Reset network activations', [
            'network_id' => $networkId,
            'neurons_reset' => $count,
        ]);

        return $count;
    }

    public function saveActivationSnapshot(int $sessionId, array $state): int
    {
        $snapshotData = [
            'activation_session_id' => $sessionId,
            'snapshot_data' => json_encode($state),
            'neuron_count' => count($state),
            'active_count' => count(array_filter($state, fn ($n) => ($n['activation_level'] ?? 0) > 0)),
            'snapshot_timestamp' => now(),
        ];

        $snapshotId = Criteria::create('activation_snapshots', $snapshotData);

        Log::info('Saved activation snapshot', [
            'snapshot_id' => $snapshotId,
            'session_id' => $sessionId,
            'neuron_count' => $snapshotData['neuron_count'],
        ]);

        return $snapshotId;
    }

    public function restoreActivationSnapshot(int $snapshotId): bool
    {
        $snapshot = Criteria::table('activation_snapshots')
            ->where('id', $snapshotId)
            ->first();

        if (! $snapshot) {
            throw new RuntimeException("Snapshot not found: {$snapshotId}");
        }

        $state = json_decode($snapshot->snapshot_data, true);

        $client = GraphCriteria::node('Neuron')->getClient();

        $restored = 0;
        foreach ($state as $neuronData) {
            $neuronId = $neuronData['neuron_id'];
            $level = $neuronData['activation_level'] ?? 0.0;

            $query = 'MATCH (n:Neuron) WHERE id(n) = $neuronId SET n.activation_level = $level RETURN n';
            $result = $client->run($query, [
                'neuronId' => $neuronId,
                'level' => $level,
            ]);

            if ($result->count() > 0) {
                $restored++;
            }
        }

        Log::info('Restored activation snapshot', [
            'snapshot_id' => $snapshotId,
            'neurons_restored' => $restored,
        ]);

        return $restored > 0;
    }

    public function getActivationHistory(int $neuronId, int $sessionId): Collection
    {
        return Criteria::table('activation_snapshots')
            ->where('activation_session_id', $sessionId)
            ->orderBy('snapshot_timestamp', 'asc')
            ->get()
            ->map(function ($snapshot) use ($neuronId) {
                $state = json_decode($snapshot->snapshot_data, true);
                $neuronState = collect($state)->firstWhere('neuron_id', $neuronId);

                return (object) [
                    'timestamp' => $snapshot->snapshot_timestamp,
                    'activation_level' => $neuronState['activation_level'] ?? 0.0,
                    'snapshot_id' => $snapshot->id,
                ];
            });
    }

    public function getCurrentState(int $networkId): array
    {
        $neurons = GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->returnClause('id(n) as neuron_id, n.name as name, n.layer as layer, n.activation_level as level, n.threshold as threshold')
            ->get();

        return $neurons->map(function ($neuron) {
            return [
                'neuron_id' => $neuron->neuron_id,
                'name' => $neuron->name,
                'layer' => $neuron->layer,
                'activation_level' => $neuron->level ?? 0.0,
                'threshold' => $neuron->threshold ?? 0.5,
            ];
        })->toArray();
    }

    public function getActivationStatistics(int $networkId): array
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE n.column_id = $networkId
                  RETURN
                    count(n) as total_neurons,
                    sum(CASE WHEN n.activation_level > 0 THEN 1 ELSE 0 END) as active_neurons,
                    avg(n.activation_level) as avg_activation,
                    max(n.activation_level) as max_activation,
                    min(n.activation_level) as min_activation';

        $result = $client->run($query, ['networkId' => $networkId]);

        $record = $result->first();

        return [
            'total_neurons' => $record?->get('total_neurons') ?? 0,
            'active_neurons' => $record?->get('active_neurons') ?? 0,
            'avg_activation' => round($record?->get('avg_activation') ?? 0.0, 4),
            'max_activation' => $record?->get('max_activation') ?? 0.0,
            'min_activation' => $record?->get('min_activation') ?? 0.0,
        ];
    }

    public function getLayerActivationStats(int $networkId): array
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron) WHERE n.column_id = $networkId
                  RETURN
                    n.layer as layer,
                    count(n) as neuron_count,
                    sum(CASE WHEN n.activation_level > 0 THEN 1 ELSE 0 END) as active_count,
                    avg(n.activation_level) as avg_activation
                  ORDER BY n.layer';

        $result = $client->run($query, ['networkId' => $networkId]);

        $stats = [];
        foreach ($result as $record) {
            $layer = $record->get('layer');
            $stats["layer_{$layer}"] = [
                'neuron_count' => $record->get('neuron_count'),
                'active_count' => $record->get('active_count'),
                'avg_activation' => round($record->get('avg_activation') ?? 0.0, 4),
            ];
        }

        return $stats;
    }

    public function updateSessionActivationCount(int $sessionId): void
    {
        $session = Criteria::table('activation_sessions')
            ->where('id', $sessionId)
            ->first();

        if (! $session) {
            return;
        }

        Criteria::table('activation_sessions')
            ->where('id', $sessionId)
            ->update([
                'activation_count' => ($session->activation_count ?? 0) + 1,
            ]);
    }

    public function setMultipleActivations(array $activations): int
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $updated = 0;
        foreach ($activations as $neuronId => $level) {
            if ($level < 0.0 || $level > 1.0) {
                continue;
            }

            $query = 'MATCH (n:Neuron) WHERE id(n) = $neuronId SET n.activation_level = $level RETURN n';
            $result = $client->run($query, [
                'neuronId' => $neuronId,
                'level' => $level,
            ]);

            if ($result->count() > 0) {
                $updated++;
            }
        }

        Log::info('Set multiple neuron activations', [
            'neurons_updated' => $updated,
            'total_requested' => count($activations),
        ]);

        return $updated;
    }

    public function getNeuronsByActivationRange(int $networkId, float $minLevel, float $maxLevel): Collection
    {
        return GraphCriteria::node('Neuron')
            ->where('n.column_id', '=', $networkId)
            ->where('n.activation_level', '>=', $minLevel)
            ->where('n.activation_level', '<=', $maxLevel)
            ->returnClause('n, id(n) as neuron_id, n.activation_level as level')
            ->get();
    }
}
