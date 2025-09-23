<?php

namespace App\Repositories;

use App\Database\GraphCriteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ColumnConnectionRepository
{
    public function createConnection(int $fromNeuronId, int $toNeuronId, string $type = 'CONNECTS_TO', array $properties = []): object
    {
        $defaultProperties = [
            'weight' => $properties['weight'] ?? 0.5,
            'strength' => $properties['strength'] ?? 1.0,
            'created_at' => now()->toISOString(),
        ];

        $mergedProperties = array_merge($defaultProperties, $properties);

        $connection = GraphCriteria::createRelation(
            $fromNeuronId,
            $toNeuronId,
            $type,
            $mergedProperties
        );

        Log::info('Created neuron connection', [
            'from' => $fromNeuronId,
            'to' => $toNeuronId,
            'type' => $type,
            'weight' => $mergedProperties['weight'],
        ]);

        return $connection;
    }

    public function getConnectionsForNeuron(int $neuronId, string $direction = 'outgoing'): Collection
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = match ($direction) {
            'outgoing' => 'MATCH (n:Neuron)-[r]->(m:Neuron) WHERE id(n) = $neuronId RETURN r, id(r) as rel_id, id(n) as start_id, id(m) as end_id, type(r) as rel_type',
            'incoming' => 'MATCH (n:Neuron)<-[r]-(m:Neuron) WHERE id(n) = $neuronId RETURN r, id(r) as rel_id, id(m) as start_id, id(n) as end_id, type(r) as rel_type',
            'all' => 'MATCH (n:Neuron)-[r]-(m:Neuron) WHERE id(n) = $neuronId RETURN r, id(r) as rel_id, id(n) as start_id, id(m) as end_id, type(r) as rel_type',
            default => throw new RuntimeException("Invalid direction: {$direction}"),
        };

        $result = $client->run($query, ['neuronId' => $neuronId]);

        return collect($result)->map(function ($record) {
            return (object) [
                'id' => $record->get('rel_id'),
                'type' => $record->get('rel_type'),
                'start_node_id' => $record->get('start_id'),
                'end_node_id' => $record->get('end_id'),
                'properties' => $record->get('r')->getProperties(),
            ];
        });
    }

    public function getConnectionsByType(string $type, ?int $networkId = null): Collection
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        if ($networkId) {
            $query = 'MATCH (n:Neuron)-[r:'.strtoupper($type).']->(m:Neuron) WHERE n.column_id = $networkId RETURN r, id(r) as rel_id, id(n) as start_id, id(m) as end_id';
            $result = $client->run($query, ['networkId' => $networkId]);
        } else {
            $query = 'MATCH (n:Neuron)-[r:'.strtoupper($type).']->(m:Neuron) RETURN r, id(r) as rel_id, id(n) as start_id, id(m) as end_id';
            $result = $client->run($query);
        }

        return collect($result)->map(function ($record) use ($type) {
            return (object) [
                'id' => $record->get('rel_id'),
                'type' => $type,
                'start_node_id' => $record->get('start_id'),
                'end_node_id' => $record->get('end_id'),
                'properties' => $record->get('r')->getProperties(),
            ];
        });
    }

    public function updateConnectionWeight(int $fromNeuronId, int $toNeuronId, float $weight): bool
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n)-[r]->(m) WHERE id(n) = $fromId AND id(m) = $toId SET r.weight = $weight RETURN r';

        $result = $client->run($query, [
            'fromId' => $fromNeuronId,
            'toId' => $toNeuronId,
            'weight' => $weight,
        ]);

        Log::info('Updated connection weight', [
            'from' => $fromNeuronId,
            'to' => $toNeuronId,
            'weight' => $weight,
        ]);

        return $result->count() > 0;
    }

    public function deleteConnection(int $fromNeuronId, int $toNeuronId): bool
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n)-[r]->(m) WHERE id(n) = $fromId AND id(m) = $toId DELETE r RETURN count(r) as deleted';

        $result = $client->run($query, [
            'fromId' => $fromNeuronId,
            'toId' => $toNeuronId,
        ]);

        $deleted = $result->first()?->get('deleted') ?? 0;

        Log::info('Deleted connection', [
            'from' => $fromNeuronId,
            'to' => $toNeuronId,
            'deleted' => $deleted,
        ]);

        return $deleted > 0;
    }

    public function getConnectionCount(int $networkId): int
    {
        $result = GraphCriteria::match('(n:Neuron)-[r]->(m:Neuron)')
            ->where('n.column_id', '=', $networkId)
            ->returnClause('count(r) as connections')
            ->first();

        return $result?->connections ?? 0;
    }

    public function findStrongestConnections(int $networkId, int $limit = 10): Collection
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron)-[r]->(m:Neuron)
                  WHERE n.column_id = $networkId
                  RETURN r, id(r) as rel_id, id(n) as start_id, id(m) as end_id, type(r) as rel_type, r.weight as weight
                  ORDER BY r.weight DESC
                  LIMIT $limit';

        $result = $client->run($query, [
            'networkId' => $networkId,
            'limit' => $limit,
        ]);

        return collect($result)->map(function ($record) {
            return (object) [
                'id' => $record->get('rel_id'),
                'type' => $record->get('rel_type'),
                'start_node_id' => $record->get('start_id'),
                'end_node_id' => $record->get('end_id'),
                'weight' => $record->get('weight'),
                'properties' => $record->get('r')->getProperties(),
            ];
        });
    }

    public function getConnectionStatistics(int $networkId): array
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        // Get total connections
        $totalQuery = 'MATCH (n:Neuron)-[r]->(m:Neuron) WHERE n.column_id = $networkId RETURN count(r) as total';
        $totalResult = $client->run($totalQuery, ['networkId' => $networkId]);
        $total = $totalResult->first()?->get('total') ?? 0;

        // Get connections by type
        $typeQuery = 'MATCH (n:Neuron)-[r]->(m:Neuron) WHERE n.column_id = $networkId RETURN type(r) as type, count(r) as count';
        $typeResult = $client->run($typeQuery, ['networkId' => $networkId]);

        $byType = [];
        foreach ($typeResult as $record) {
            $byType[$record->get('type')] = $record->get('count');
        }

        // Get average weight
        $weightQuery = 'MATCH (n:Neuron)-[r]->(m:Neuron) WHERE n.column_id = $networkId AND EXISTS(r.weight) RETURN avg(r.weight) as avg_weight, min(r.weight) as min_weight, max(r.weight) as max_weight';
        $weightResult = $client->run($weightQuery, ['networkId' => $networkId]);
        $weightRecord = $weightResult->first();

        return [
            'total_connections' => $total,
            'by_type' => $byType,
            'average_weight' => $weightRecord?->get('avg_weight') ?? 0,
            'min_weight' => $weightRecord?->get('min_weight') ?? 0,
            'max_weight' => $weightRecord?->get('max_weight') ?? 0,
        ];
    }

    public function getConnectionPath(int $startNeuronId, int $endNeuronId, int $maxDepth = 5): ?array
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH path = shortestPath((start:Neuron)-[*..'.($maxDepth).']-(end:Neuron))
                  WHERE id(start) = $startId AND id(end) = $endId
                  RETURN [node in nodes(path) | id(node)] as node_ids,
                         [rel in relationships(path) | {type: type(rel), weight: rel.weight}] as rels,
                         length(path) as path_length';

        $result = $client->run($query, [
            'startId' => $startNeuronId,
            'endId' => $endNeuronId,
        ]);

        if ($result->count() === 0) {
            return null;
        }

        $record = $result->first();

        return [
            'node_ids' => $record->get('node_ids'),
            'relationships' => $record->get('rels'),
            'path_length' => $record->get('path_length'),
        ];
    }

    public function deleteAllConnections(int $networkId): int
    {
        $client = GraphCriteria::node('Neuron')->getClient();

        $query = 'MATCH (n:Neuron)-[r]-(m:Neuron) WHERE n.column_id = $networkId DELETE r RETURN count(r) as deleted';

        $result = $client->run($query, ['networkId' => $networkId]);

        $deleted = $result->first()?->get('deleted') ?? 0;

        Log::info('Deleted all connections for network', [
            'network_id' => $networkId,
            'deleted' => $deleted,
        ]);

        return $deleted;
    }
}
