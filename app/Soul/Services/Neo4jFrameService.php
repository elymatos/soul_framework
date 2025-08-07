<?php

namespace App\Soul\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Exception\Neo4jException as LaudisNeo4jException;
use App\Soul\FrameInstance;
use App\Soul\Contracts\Neo4jService;
use App\Soul\Exceptions\Neo4jException;
use App\Soul\Exceptions\Neo4jConnectionException;
use App\Soul\Exceptions\Neo4jQueryException;

/**
 * Neo4jFrameService - Manages frame instances in Neo4j database
 * 
 * This service handles all Neo4j operations for the SOUL framework including
 * frame instance persistence, relationship management, and session archival.
 */
class Neo4jFrameService implements Neo4jService
{
    protected ClientInterface $neo4j;
    protected array $nodeLabels;
    protected array $relationshipTypes;

    public function __construct(ClientInterface $neo4j)
    {
        $this->neo4j = $neo4j;
        
        // Define node labels used by the SOUL framework
        $this->nodeLabels = [
            'FrameInstance' => 'FrameInstance',
            'ProcessingSession' => 'ProcessingSession',
            'FrameElement' => 'FrameElement'
        ];

        // Define relationship types
        $this->relationshipTypes = [
            'HAS_FRAME_ELEMENT' => 'HAS_FRAME_ELEMENT',
            'RELATES_TO' => 'RELATES_TO',
            'INSTANTIATED_FROM' => 'INSTANTIATED_FROM',
            'BELONGS_TO_SESSION' => 'BELONGS_TO_SESSION',
            'COMMUNICATES_WITH' => 'COMMUNICATES_WITH'
        ];
    }

    /**
     * Create a node for a frame instance
     */
    public function createFrameInstanceNode(FrameInstance $instance, string $sessionId): bool
    {
        try {
            $query = '
                CREATE (fi:FrameInstance {
                    instance_id: $instance_id,
                    frame_id: $frame_id,
                    label: $label,
                    type: $type,
                    session_id: $session_id,
                    created_at: datetime(),
                    status: "active"
                })
                RETURN fi.instance_id as created_id
            ';

            $parameters = [
                'instance_id' => $instance->getInstanceId(),
                'frame_id' => $instance->getFrameId(),
                'label' => $instance->getLabel(),
                'type' => $instance->getType(),
                'session_id' => $sessionId
            ];

            $result = $this->neo4j->run($query, $parameters);
            
            if ($result->count() > 0) {
                // Create nodes for frame elements
                $this->createFrameElementNodes($instance);
                
                Log::debug("SOUL Neo4j: Created frame instance node", [
                    'instance_id' => $instance->getInstanceId(),
                    'session_id' => $sessionId
                ]);
                
                return true;
            }
            
            return false;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to create frame instance node");
            return false;
        }
    }

    /**
     * Delete a frame instance node
     */
    public function deleteFrameInstanceNode(string $instanceId): bool
    {
        try {
            $query = '
                MATCH (fi:FrameInstance {instance_id: $instance_id})
                OPTIONAL MATCH (fi)-[:HAS_FRAME_ELEMENT]->(fe:FrameElement)
                DETACH DELETE fi, fe
                RETURN count(fi) as deleted_count
            ';

            $result = $this->neo4j->run($query, ['instance_id' => $instanceId]);
            $deletedCount = $result->first()->get('deleted_count');

            Log::debug("SOUL Neo4j: Deleted frame instance node", [
                'instance_id' => $instanceId,
                'deleted_count' => $deletedCount
            ]);

            return $deletedCount > 0;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to delete frame instance node");
            return false;
        }
    }

    /**
     * Create relationship between frame instances
     */
    public function createInstanceRelationship(
        string $fromInstanceId,
        string $toInstanceId,
        string $relationshipType,
        array $properties = []
    ): bool {
        try {
            // Build properties string for Cypher query
            $propertiesClause = $this->buildPropertiesClause($properties);

            $query = "
                MATCH (from:FrameInstance {instance_id: \$from_id})
                MATCH (to:FrameInstance {instance_id: \$to_id})
                CREATE (from)-[r:{$relationshipType} {$propertiesClause}]->(to)
                RETURN r
            ";

            $parameters = array_merge([
                'from_id' => $fromInstanceId,
                'to_id' => $toInstanceId
            ], $properties);

            $result = $this->neo4j->run($query, $parameters);

            Log::debug("SOUL Neo4j: Created instance relationship", [
                'from' => $fromInstanceId,
                'to' => $toInstanceId,
                'type' => $relationshipType
            ]);

            return $result->count() > 0;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to create instance relationship");
            return false;
        }
    }

    /**
     * Archive a processing session
     */
    public function archiveProcessingSession(array $session): bool
    {
        try {
            $query = '
                CREATE (ps:ProcessingSession {
                    session_id: $session_id,
                    started_at: $started_at,
                    ended_at: $ended_at,
                    input: $input,
                    status: $status,
                    instances_count: $instances_count,
                    archived_at: datetime()
                })
                WITH ps
                MATCH (fi:FrameInstance {session_id: $session_id})
                CREATE (ps)-[:CONTAINS_INSTANCE]->(fi)
                RETURN ps.session_id as archived_session
            ';

            $parameters = [
                'session_id' => $session['id'],
                'started_at' => $session['started_at']->toISOString(),
                'ended_at' => $session['ended_at']->toISOString(),
                'input' => json_encode($session['input']),
                'status' => $session['status'],
                'instances_count' => $session['instances']->count()
            ];

            $result = $this->neo4j->run($query, $parameters);

            Log::info("SOUL Neo4j: Archived processing session", [
                'session_id' => $session['id'],
                'instances_count' => $session['instances']->count()
            ]);

            return $result->count() > 0;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to archive processing session");
            return false;
        }
    }

    /**
     * Query frame instances by criteria
     */
    public function queryFrameInstances(array $criteria): Collection
    {
        try {
            $whereClause = $this->buildWhereClause($criteria);
            
            $query = "
                MATCH (fi:FrameInstance)
                {$whereClause}
                RETURN fi.instance_id as instance_id,
                       fi.frame_id as frame_id,
                       fi.label as label,
                       fi.type as type,
                       fi.session_id as session_id,
                       fi.created_at as created_at,
                       fi.status as status
                ORDER BY fi.created_at DESC
                LIMIT 100
            ";

            $result = $this->neo4j->run($query, $criteria);
            $instances = new Collection();

            foreach ($result as $record) {
                $instances->push([
                    'instance_id' => $record->get('instance_id'),
                    'frame_id' => $record->get('frame_id'),
                    'label' => $record->get('label'),
                    'type' => $record->get('type'),
                    'session_id' => $record->get('session_id'),
                    'created_at' => $record->get('created_at'),
                    'status' => $record->get('status')
                ]);
            }

            Log::debug("SOUL Neo4j: Queried frame instances", [
                'criteria' => $criteria,
                'results_count' => $instances->count()
            ]);

            return $instances;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to query frame instances");
            return new Collection();
        }
    }

    /**
     * Get instance relationships
     */
    public function getInstanceRelationships(string $instanceId): Collection
    {
        try {
            $query = '
                MATCH (fi:FrameInstance {instance_id: $instance_id})
                MATCH (fi)-[r]-(other:FrameInstance)
                RETURN type(r) as relationship_type,
                       r as relationship_properties,
                       other.instance_id as other_instance_id,
                       other.frame_id as other_frame_id,
                       other.label as other_label,
                       other.type as other_type,
                       startNode(r) = fi as is_outgoing
            ';

            $result = $this->neo4j->run($query, ['instance_id' => $instanceId]);
            $relationships = new Collection();

            foreach ($result as $record) {
                $relationships->push([
                    'relationship_type' => $record->get('relationship_type'),
                    'properties' => $record->get('relationship_properties')->toArray(),
                    'other_instance' => [
                        'instance_id' => $record->get('other_instance_id'),
                        'frame_id' => $record->get('other_frame_id'),
                        'label' => $record->get('other_label'),
                        'type' => $record->get('other_type')
                    ],
                    'is_outgoing' => $record->get('is_outgoing')
                ]);
            }

            Log::debug("SOUL Neo4j: Retrieved instance relationships", [
                'instance_id' => $instanceId,
                'relationships_count' => $relationships->count()
            ]);

            return $relationships;

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to get instance relationships");
            return new Collection();
        }
    }

    /**
     * Get database statistics
     */
    public function getDatabaseStatistics(): array
    {
        try {
            $query = '
                MATCH (fi:FrameInstance)
                WITH count(fi) as frame_instances
                MATCH (ps:ProcessingSession)
                WITH frame_instances, count(ps) as sessions
                MATCH ()-[r]->()
                RETURN frame_instances, sessions, count(r) as relationships
            ';

            $result = $this->neo4j->run($query);
            
            if ($result->count() > 0) {
                $record = $result->first();
                return [
                    'frame_instances' => $record->get('frame_instances'),
                    'processing_sessions' => $record->get('sessions'),
                    'relationships' => $record->get('relationships')
                ];
            }

            return [
                'frame_instances' => 0,
                'processing_sessions' => 0,
                'relationships' => 0
            ];

        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to get database statistics");
            return [
                'frame_instances' => 0,
                'processing_sessions' => 0,
                'relationships' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create frame element nodes for a frame instance
     */
    protected function createFrameElementNodes(FrameInstance $instance): void
    {
        $frameElements = $instance->getFrameElements();
        
        foreach ($frameElements as $fe) {
            try {
                $query = '
                    MATCH (fi:FrameInstance {instance_id: $instance_id})
                    CREATE (fe:FrameElement {
                        name: $name,
                        fe_type: $fe_type,
                        description: $description,
                        required: $required,
                        has_value: $has_value,
                        value: $value
                    })
                    CREATE (fi)-[:HAS_FRAME_ELEMENT]->(fe)
                ';

                $parameters = [
                    'instance_id' => $instance->getInstanceId(),
                    'name' => $fe->getName(),
                    'fe_type' => $fe->getFeType(),
                    'description' => $fe->getDescription() ?? '',
                    'required' => $fe->isRequired(),
                    'has_value' => $fe->hasValue(),
                    'value' => $fe->hasValue() ? json_encode($fe->getValue()) : null
                ];

                $this->neo4j->run($query, $parameters);

            } catch (LaudisNeo4jException $e) {
                Log::warning("SOUL Neo4j: Failed to create frame element node", [
                    'instance_id' => $instance->getInstanceId(),
                    'element_name' => $fe->getName(),
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Build WHERE clause from criteria array
     */
    protected function buildWhereClause(array $criteria): string
    {
        if (empty($criteria)) {
            return '';
        }

        $conditions = [];
        foreach ($criteria as $key => $value) {
            switch ($key) {
                case 'frame_id':
                case 'type':
                case 'session_id':
                case 'status':
                    $conditions[] = "fi.{$key} = \${$key}";
                    break;
                case 'created_after':
                    $conditions[] = "fi.created_at > datetime(\${$key})";
                    break;
                case 'created_before':
                    $conditions[] = "fi.created_at < datetime(\${$key})";
                    break;
            }
        }

        return !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    }

    /**
     * Build properties clause for Cypher query
     */
    protected function buildPropertiesClause(array $properties): string
    {
        if (empty($properties)) {
            return 'created_at: datetime()';
        }

        $propStrings = ['created_at: datetime()'];
        foreach ($properties as $key => $value) {
            if (!in_array($key, ['from_id', 'to_id'])) {
                $propStrings[] = "{$key}: \${$key}";
            }
        }

        return implode(', ', $propStrings);
    }

    /**
     * Run spreading activation from initial concepts
     */
    public function runSpreadingActivation(array $initialConcepts, array $options = []): array
    {
        $maxDepth = $options['max_depth'] ?? 3;
        $threshold = $options['activation_threshold'] ?? 0.1;
        $includeProcAgents = $options['include_procedural_agents'] ?? false;
        
        try {
            $query = "
                WITH \$concepts AS initial_concepts
                UNWIND initial_concepts AS concept_name
                MATCH (start:Concept {name: concept_name})
                
                CALL apoc.path.subgraphAll(start, {
                    maxLevel: \$maxDepth,
                    relationshipFilter: 'IS_A|PART_OF|CAUSES|SIMILAR_TO|HAS_FE',
                    labelFilter: '" . ($includeProcAgents ? 'Concept|PROCEDURAL_AGENT' : 'Concept') . "'
                })
                YIELD nodes, relationships
                
                UNWIND nodes AS node
                WITH node, 
                     CASE 
                         WHEN node IN [start] THEN 1.0
                         ELSE 1.0 / (apoc.node.degree(node) + 1.0)
                     END AS activation_strength
                WHERE activation_strength >= \$threshold
                
                RETURN collect({
                    id: id(node),
                    name: node.name,
                    type: labels(node)[0],
                    properties: properties(node),
                    activation_strength: activation_strength
                }) AS activated_nodes
            ";
            
            $result = $this->neo4j->run($query, [
                'concepts' => $initialConcepts,
                'maxDepth' => $maxDepth,
                'threshold' => $threshold
            ]);
            
            $activatedNodes = $result->first()->get('activated_nodes');
            
            return [
                'activated_nodes' => $activatedNodes,
                'initial_concepts' => $initialConcepts,
                'total_nodes' => count($activatedNodes),
                'max_depth' => $maxDepth
            ];
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to run spreading activation");
            return [
                'activated_nodes' => [],
                'initial_concepts' => $initialConcepts,
                'total_nodes' => 0,
                'max_depth' => $maxDepth
            ];
        }
    }
    
    /**
     * Find procedural agent nodes by code reference
     */
    public function findProceduralAgent(string $codeReference): ?array
    {
        try {
            $query = "
                MATCH (agent:PROCEDURAL_AGENT {code_reference: \$codeRef})
                RETURN {
                    id: id(agent),
                    name: agent.name,
                    code_reference: agent.code_reference,
                    properties: properties(agent)
                } AS agent
            ";
            
            $result = $this->neo4j->run($query, ['codeRef' => $codeReference]);
            
            return $result->first()?->get('agent');
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to find procedural agent");
            return null;
        }
    }
    
    /**
     * Record successful activation path as K-line
     */
    public function recordKLine(array $activationPath, string $context): string
    {
        $klineId = 'kline_' . uniqid();
        
        try {
            $query = "
                CREATE (kline:KLine {
                    id: \$klineId,
                    context: \$context,
                    activation_pattern: \$activationPattern,
                    agent_sequence: \$agentSequence,
                    success_rate: \$successRate,
                    usage_count: 1,
                    created_at: datetime(),
                    last_used: datetime()
                })
                
                WITH kline
                UNWIND \$nodeIds AS nodeId
                MATCH (node) WHERE id(node) = nodeId
                CREATE (kline)-[:ACTIVATES {strength: 1.0}]->(node)
                
                RETURN kline.id AS id
            ";
            
            $nodeIds = array_column($activationPath['activation_pattern']['activated_nodes'] ?? [], 'id');
            
            $result = $this->neo4j->run($query, [
                'klineId' => $klineId,
                'context' => $context,
                'activationPattern' => json_encode($activationPath['activation_pattern']),
                'agentSequence' => $activationPath['agent_sequence'],
                'successRate' => $activationPath['success_metrics']['success_rate'],
                'nodeIds' => $nodeIds
            ]);
            
            return $result->first()->get('id');
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to record K-line");
            return $klineId; // Return ID even if failed
        }
    }
    
    /**
     * Create concept node
     */
    public function createConceptNode(array $conceptData): string
    {
        try {
            $labels = $conceptData['labels'] ?? ['Concept'];
            $properties = $conceptData['properties'] ?? [];
            $properties['created_at'] = now()->toISOString();
            
            $labelStr = ':' . implode(':', $labels);
            $query = "
                CREATE (c{$labelStr} \$properties)
                RETURN id(c) AS id, c.name AS name
            ";
            
            $result = $this->neo4j->run($query, ['properties' => $properties]);
            
            return $result->first()->get('id');
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to create concept node");
            throw $e;
        }
    }
    
    /**
     * Create procedural agent node
     */
    public function createProceduralAgent(array $agentData): string
    {
        try {
            $query = "
                CREATE (agent:PROCEDURAL_AGENT:Concept {
                    name: \$name,
                    code_reference: \$codeReference,
                    description: \$description,
                    priority: \$priority,
                    created_at: datetime()
                })
                RETURN id(agent) AS id
            ";
            
            $result = $this->neo4j->run($query, [
                'name' => $agentData['name'],
                'codeReference' => $agentData['code_reference'],
                'description' => $agentData['description'] ?? '',
                'priority' => $agentData['priority'] ?? 1
            ]);
            
            return $result->first()->get('id');
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to create procedural agent");
            throw $e;
        }
    }
    
    /**
     * Strengthen K-line based on usage
     */
    public function strengthenKLine(string $klineId): void
    {
        try {
            $query = "
                MATCH (kline:KLine {id: \$klineId})
                SET kline.usage_count = kline.usage_count + 1,
                    kline.last_used = datetime()
                
                MATCH (kline)-[r:ACTIVATES]->(node)
                SET r.strength = r.strength * 1.1
            ";
            
            $this->neo4j->run($query, ['klineId' => $klineId]);
            
        } catch (LaudisNeo4jException $e) {
            $this->handleNeo4jException($e, "Failed to strengthen K-line");
        }
    }

    /**
     * Handle Neo4j exceptions with proper SOUL exception types
     */
    protected function handleNeo4jException(LaudisNeo4jException $e, string $operation): void
    {
        $message = "{$operation}: " . $e->getMessage();
        
        Log::error("SOUL Neo4j: Operation failed", [
            'operation' => $operation,
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ]);

        // Map to appropriate SOUL exception type
        if (str_contains($e->getMessage(), 'connection') || str_contains($e->getMessage(), 'timeout')) {
            throw new Neo4jConnectionException($message, $e->getCode(), $e);
        } else {
            throw new Neo4jQueryException($message, $e->getCode(), $e);
        }
    }
}