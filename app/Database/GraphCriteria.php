<?php

namespace App\Database;

use App\Services\Neo4j\ConnectionService;
use App\Services\Neo4j\QueryBuilderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\Result;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;
use Laudis\Neo4j\Types\Relationship;
use RuntimeException;

class GraphCriteria
{
    private ClientInterface $client;

    private QueryBuilderService $queryBuilder;

    private string $connectionName;

    private bool $logQueries;

    public static string $connection = '';

    public function __construct(?string $connectionName = null)
    {
        $this->connectionName = $connectionName ?? config('neo4j.default');
        $this->client = ConnectionService::connection($this->connectionName);
        $this->queryBuilder = new QueryBuilderService;
        $this->logQueries = config('neo4j.logging.enabled', false);
    }

    /**
     * Create a new GraphCriteria instance with node selection
     */
    public static function node(string $label, string $variable = 'n'): static
    {
        $self = new self(self::$connection ?: null);
        $nodePattern = QueryBuilderService::nodePattern($variable, $label);
        $self->queryBuilder->match($nodePattern);

        return $self;
    }

    /**
     * Create a new GraphCriteria instance with custom match pattern
     */
    public static function match(string $pattern): static
    {
        $self = new self(self::$connection ?: null);
        $self->queryBuilder->match($pattern);

        return $self;
    }

    /**
     * Create a new GraphCriteria instance for creating nodes
     */
    public static function createNode(string $label, array $properties = [], string $variable = 'n'): ?object
    {
        $self = new self(self::$connection ?: null);

        // Add properties to query builder as parameters
        $propertyParams = [];
        foreach ($properties as $key => $value) {
            $paramKey = $self->queryBuilder->addParameter($value);
            $propertyParams[] = "{$key}: \${$paramKey}";
        }

        // Add created_at timestamp
        if (! isset($properties['created_at'])) {
            $timestampParam = $self->queryBuilder->addParameter(now()->toISOString());
            $propertyParams[] = "created_at: \${$timestampParam}";
        }

        $propertiesString = ! empty($propertyParams) ? ' {'.implode(', ', $propertyParams).'}' : '';
        $createPattern = "({$variable}:{$label}{$propertiesString})";

        $self->queryBuilder->create($createPattern)
            ->returnClause($variable);

        $result = $self->execute();
        $record = $result->first();
        if ($record) {
            $node = $record->get($variable);

            return $self->processValue($node);
        }

        return null;
    }

    /**
     * Create a relationship between two nodes
     */
    public static function createRelation(mixed $fromNodeId, mixed $toNodeId, string $relationType, array $properties = []): ?object
    {
        $self = new self(self::$connection ?: null);

        // Build the relationship creation query
        $fromParam = $self->queryBuilder->addParameter($fromNodeId);
        $toParam = $self->queryBuilder->addParameter($toNodeId);

        // Add properties to query builder as parameters
        $propertyParams = [];
        foreach ($properties as $key => $value) {
            $paramKey = $self->queryBuilder->addParameter($value);
            $propertyParams[] = "{$key}: \${$paramKey}";
        }

        // Add created_at timestamp
        $timestampParam = $self->queryBuilder->addParameter(now()->toISOString());
        $propertyParams[] = "created_at: \${$timestampParam}";

        $propertiesString = ! empty($propertyParams) ? ' {'.implode(', ', $propertyParams).'}' : '';

        // Build the complete query manually for relationship creation
        $query = "MATCH (from), (to) WHERE ID(from) = \${$fromParam} AND ID(to) = \${$toParam} CREATE (from)-[r:{$relationType}{$propertiesString}]->(to) RETURN r";

        // Execute directly with the manually built query
        $result = $self->client->run($query, $self->queryBuilder->getParameters());
        $record = $result->first();
        if ($record) {
            $relationship = $record->get('r');

            return $self->processValue($relationship);
        }

        return null;
    }

    /**
     * Add WHERE condition
     */
    public function where(string $field, string $operator = '=', mixed $value = null): self
    {
        if (func_num_args() > 2) {
            $this->queryBuilder->whereParameter($field, $operator, $value);
        } else {
            $this->queryBuilder->where($field);
        }

        return $this;
    }

    /**
     * Add relationships to the query
     */
    public function withRelations(string $relationType, string $direction = 'outgoing', string $targetLabel = '', string $relVariable = 'r'): self
    {
        $relationPattern = QueryBuilderService::relationshipPattern($relationType, $relVariable);

        switch ($direction) {
            case 'incoming':
                $pattern = "({$targetLabel})-{$relationPattern}->(n)";
                break;
            case 'bidirectional':
                $pattern = "({$targetLabel})-{$relationPattern}-(n)";
                break;
            default: // outgoing
                $pattern = "(n)-{$relationPattern}->({$targetLabel})";
                break;
        }

        $this->queryBuilder->match($pattern);

        return $this;
    }

    /**
     * Set what to return
     */
    public function returnClause(string $expression): self
    {
        $this->queryBuilder->returnClause($expression);

        return $this;
    }

    /**
     * Order results
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->queryBuilder->orderBy($field, $direction);

        return $this;
    }

    /**
     * Limit results
     */
    public function limit(int $count): self
    {
        $this->queryBuilder->limit($count);

        return $this;
    }

    /**
     * Skip results
     */
    public function skip(int $count): self
    {
        $this->queryBuilder->skip($count);

        return $this;
    }

    /**
     * Execute the query and return all results
     */
    public function get(): Collection
    {
        // Ensure we have a RETURN clause if none was explicitly set
        $query = $this->queryBuilder->build();
        if (! str_contains(strtoupper($query), 'RETURN')) {
            $this->queryBuilder->returnClause('n');
        }

        $result = $this->execute();
        if ($result instanceof SummarizedResult) {
            return collect();
        }

        return $this->processResults($result);
    }

    /**
     * Execute the query and return the first result
     */
    public function first(): ?object
    {
        $this->limit(1);
        $result = $this->execute();
        if ($result instanceof SummarizedResult) {
            return null;
        }
        $processed = $this->processResults($result);

        return $processed->first();
    }

    /**
     * Get results as array
     */
    public function all(): array
    {
        return $this->get()->all();
    }

    /**
     * Count results
     */
    public function count(): int
    {
        $originalQuery = $this->queryBuilder->build();
        $this->queryBuilder->reset();

        // Parse the original query to extract MATCH clauses
        if (preg_match('/MATCH\s+(.+?)(?:\s+WHERE|\s+RETURN|\s+ORDER|\s+LIMIT|$)/i', $originalQuery, $matches)) {
            $this->queryBuilder->match($matches[1]);
        }

        $this->queryBuilder->returnClause('count(n) as count');

        $result = $this->execute();
        $record = $result->first();

        return $record ? $record->get('count') : 0;
    }

    /**
     * Delete nodes/relationships
     */
    public function delete(): int
    {
        $this->queryBuilder->delete('n');
        $result = $this->execute();

        // Return the number of nodes deleted (Neo4j provides this info in summary)
        return $result->getSummary()->getCounters()->nodesDeleted();
    }

    /**
     * Update node properties
     */
    public function update(array $properties): int
    {
        $setParts = [];
        foreach ($properties as $key => $value) {
            $paramKey = $this->queryBuilder->addParameter($value);
            $setParts[] = "n.{$key} = \${$paramKey}";
        }

        if (! empty($setParts)) {
            $this->queryBuilder->set(implode(', ', $setParts))
                ->returnClause('n');
        }

        $result = $this->execute();

        return $result->getSummary()->getCounters()->propertiesSet();
    }

    /**
     * Execute the built query
     */
    private function execute(): Result|SummarizedResult
    {
        $query = $this->queryBuilder->build();
        $parameters = $this->queryBuilder->getParameters();

        $this->logQuery($query, $parameters);

        try {
            return $this->client->run($query, $parameters);
        } catch (\Exception $e) {
            $this->logQueryError($query, $parameters, $e);
            throw new RuntimeException('Graph query failed: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Process Neo4j results into objects
     */
    private function processResults(Result $result): Collection
    {
        $results = collect();

        foreach ($result as $record) {
            $data = [];

            foreach ($record->keys() as $key) {
                $value = $record->get($key);
                $data[$key] = $this->processValue($value);
            }

            $results->push((object) $data);
        }

        return $results;
    }

    /**
     * Process individual values from Neo4j results
     */
    private function processValue($value)
    {
        if ($value instanceof Node) {
            return $this->nodeToObject($value);
        } elseif ($value instanceof Relationship) {
            return $this->relationshipToObject($value);
        } elseif ($value instanceof CypherMap) {
            return $this->mapToObject($value);
        } elseif (is_array($value)) {
            return array_map([$this, 'processValue'], $value);
        }

        return $value;
    }

    /**
     * Convert Neo4j Node to object
     */
    private function nodeToObject(Node $node): object
    {
        $data = [
            'id' => $node->getId(),
            'labels' => $node->getLabels()->toArray(),
            'properties' => $node->getProperties()->toArray(),
        ];

        return (object) $data;
    }

    /**
     * Convert Neo4j Relationship to object
     */
    private function relationshipToObject(Relationship $relationship): object
    {
        $data = [
            'id' => $relationship->getId(),
            'type' => $relationship->getType(),
            'start_node_id' => $relationship->getStartNodeId(),
            'end_node_id' => $relationship->getEndNodeId(),
            'properties' => $relationship->getProperties()->toArray(),
        ];

        return (object) $data;
    }

    /**
     * Convert CypherMap to object
     */
    private function mapToObject(CypherMap $map): object
    {
        return (object) $map->toArray();
    }

    /**
     * Log query execution
     */
    private function logQuery(string $query, array $parameters): void
    {
        if (! $this->logQueries) {
            return;
        }

        Log::channel(config('neo4j.logging.channel', 'daily'))
            ->log(config('neo4j.logging.level', 'debug'), 'Neo4j Query Executed', [
                'connection' => $this->connectionName,
                'query' => $query,
                'parameters' => $parameters,
                'time' => microtime(true),
            ]);
    }

    /**
     * Log query error
     */
    private function logQueryError(string $query, array $parameters, \Exception $e): void
    {
        Log::channel(config('neo4j.logging.channel', 'daily'))
            ->error('Neo4j Query Failed', [
                'connection' => $this->connectionName,
                'query' => $query,
                'parameters' => $parameters,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
    }

    /**
     * Get the raw query builder for advanced operations
     */
    public function getQueryBuilder(): QueryBuilderService
    {
        return $this->queryBuilder;
    }

    /**
     * Get the client connection
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }
}
