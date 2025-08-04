<?php

namespace App\Services\SOUL;

use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * SOUL Graph Service
 * 
 * Handles graph visualization and analysis operations for SOUL Framework,
 * including graph generation for frontend visualization and data export.
 */
class GraphService
{
    public function __construct(
        private ClientInterface $neo4j
    ) {}

    /**
     * Generate graph visualization data for a concept and its neighborhood
     */
    public function getConceptGraphVisualization(string $conceptName, int $depth = 2): array
    {
        $query = sprintf(
            'MATCH path = (center:Concept {name: $conceptName})-[*0..%d]-(connected:Concept)
             WITH center, connected, relationships(path) as rels
             RETURN DISTINCT 
                connected.name as name,
                connected.type as type,
                connected.category as category,
                connected.is_primitive as isPrimitive,
                labels(connected) as labels,
                length([r in rels WHERE r IS NOT NULL]) as distance',
            $depth
        );

        $result = $this->neo4j->run($query, ['conceptName' => $conceptName]);

        $nodes = [];
        foreach ($result as $record) {
            $nodes[] = [
                'id' => $record->get('name'),
                'name' => $record->get('name'),
                'type' => $record->get('type'),
                'category' => $record->get('category'),
                'isPrimitive' => $record->get('isPrimitive'),
                'labels' => $record->get('labels')->toArray(),
                'distance' => $record->get('distance'),
                'group' => $this->getNodeGroup($record->get('type'), $record->get('isPrimitive'))
            ];
        }

        // Get relationships between these nodes
        $nodeNames = array_column($nodes, 'name');
        $relationships = $this->getRelationshipsBetweenNodes($nodeNames);

        return [
            'nodes' => $nodes,
            'links' => $relationships
        ];
    }

    /**
     * Get relationships between specific nodes
     */
    private function getRelationshipsBetweenNodes(array $nodeNames): array
    {
        if (empty($nodeNames)) {
            return [];
        }

        $query = '
            MATCH (a:Concept)-[r]->(b:Concept)
            WHERE a.name IN $nodeNames AND b.name IN $nodeNames
            RETURN 
                a.name as source,
                b.name as target,
                type(r) as relationshipType,
                coalesce(r.weight, 1.0) as weight,
                r.description as description
        ';

        $result = $this->neo4j->run($query, ['nodeNames' => $nodeNames]);

        $relationships = [];
        foreach ($result as $record) {
            $relationships[] = [
                'source' => $record->get('source'),
                'target' => $record->get('target'),
                'relationshipType' => $record->get('relationshipType'),
                'weight' => $record->get('weight'),
                'description' => $record->get('description'),
                'value' => $record->get('weight') // For visualization libraries
            ];
        }

        return $relationships;
    }

    /**
     * Get graph statistics and metrics
     */
    public function getGraphStatistics(): array
    {
        $queries = [
            'totalConcepts' => 'MATCH (c:Concept) RETURN count(c) as total',
            'totalPrimitives' => 'MATCH (p:Primitive) RETURN count(p) as total',
            'totalRelationships' => 'MATCH ()-[r]->() RETURN count(r) as total',
            'conceptsByType' => 'MATCH (c:Concept) RETURN c.type as type, count(c) as count ORDER BY count DESC',
            'relationshipsByType' => 'MATCH ()-[r]->() RETURN type(r) as type, count(r) as count ORDER BY count DESC',
            'primitivesByCategory' => 'MATCH (p:Primitive) RETURN p.type as category, count(p) as count ORDER BY count DESC'
        ];

        $statistics = [];
        foreach ($queries as $key => $query) {
            $result = $this->neo4j->run($query);
            
            if (in_array($key, ['totalConcepts', 'totalPrimitives', 'totalRelationships'])) {
                $statistics[$key] = $result->first()->get('total');
            } else {
                $statistics[$key] = [];
                foreach ($result as $record) {
                    $statistics[$key][] = [
                        'label' => $record->get($key === 'conceptsByType' ? 'type' : ($key === 'primitivesByCategory' ? 'category' : 'type')),
                        'count' => $record->get('count')
                    ];
                }
            }
        }

        return $statistics;
    }

    /**
     * Export graph data in specified format
     */
    public function exportGraph(string $format = 'json'): array
    {
        // Get all concepts
        $conceptsQuery = '
            MATCH (c:Concept)
            RETURN c.name as name, c.type as type, c.category as category,
                   c.is_primitive as isPrimitive, c.description as description,
                   labels(c) as labels, properties(c) as properties
        ';

        $conceptsResult = $this->neo4j->run($conceptsQuery);
        $concepts = [];
        foreach ($conceptsResult as $record) {
            $concepts[] = [
                'name' => $record->get('name'),
                'type' => $record->get('type'),
                'category' => $record->get('category'),
                'isPrimitive' => $record->get('isPrimitive'),
                'description' => $record->get('description'),
                'labels' => $record->get('labels')->toArray(),
                'properties' => $record->get('properties')->toArray()
            ];
        }

        // Get all relationships
        $relationshipsQuery = '
            MATCH (a:Concept)-[r]->(b:Concept)
            RETURN a.name as source, b.name as target, type(r) as type,
                   properties(r) as properties
        ';

        $relationshipsResult = $this->neo4j->run($relationshipsQuery);
        $relationships = [];
        foreach ($relationshipsResult as $record) {
            $relationships[] = [
                'source' => $record->get('source'),
                'target' => $record->get('target'),
                'type' => $record->get('type'),
                'properties' => $record->get('properties')->toArray()
            ];
        }

        $graphData = [
            'metadata' => [
                'exported_at' => now()->toISOString(),
                'total_concepts' => count($concepts),
                'total_relationships' => count($relationships),
                'format' => $format
            ],
            'concepts' => $concepts,
            'relationships' => $relationships
        ];

        return $graphData;
    }

    /**
     * Import graph data from array
     */
    public function importGraph(array $graphData): array
    {
        $imported = ['concepts' => 0, 'relationships' => 0, 'errors' => []];

        // Import concepts
        if (isset($graphData['concepts'])) {
            foreach ($graphData['concepts'] as $conceptData) {
                try {
                    $labels = isset($conceptData['labels']) ? implode(':', $conceptData['labels']) : 'Concept';
                    $properties = $conceptData;
                    unset($properties['labels']); // Remove labels from properties

                    $query = "MERGE (c:{$labels} {name: \$name}) SET c += \$properties";
                    $this->neo4j->run($query, [
                        'name' => $conceptData['name'],
                        'properties' => $properties
                    ]);
                    $imported['concepts']++;
                } catch (\Exception $e) {
                    $imported['errors'][] = "Concept '{$conceptData['name']}': " . $e->getMessage();
                }
            }
        }

        // Import relationships
        if (isset($graphData['relationships'])) {
            foreach ($graphData['relationships'] as $relData) {
                try {
                    $query = sprintf(
                        'MATCH (a:Concept {name: $source}), (b:Concept {name: $target})
                         MERGE (a)-[r:%s]->(b)
                         SET r += $properties',
                        $relData['type']
                    );
                    
                    $this->neo4j->run($query, [
                        'source' => $relData['source'],
                        'target' => $relData['target'],
                        'properties' => $relData['properties'] ?? []
                    ]);
                    $imported['relationships']++;
                } catch (\Exception $e) {
                    $imported['errors'][] = "Relationship {$relData['source']}->{$relData['target']}: " . $e->getMessage();
                }
            }
        }

        return $imported;
    }

    /**
     * Perform spreading activation from a concept
     */
    public function performSpreadingActivation(
        string $startConcept, 
        float $threshold = 0.5, 
        int $maxDepth = 3,
        int $maxResults = 50
    ): array {
        $query = sprintf(
            'MATCH path = (start:Concept {name: $startConcept})-[*1..%d]-(activated:Concept)
             WHERE all(r in relationships(path) WHERE coalesce(r.weight, 1.0) >= $threshold)
             WITH activated, 
                  min(length(path)) as minDistance,
                  avg([r in relationships(path) | coalesce(r.weight, 1.0)]) as avgWeight
             RETURN DISTINCT
                activated.name as name,
                activated.type as type,
                activated.description as description,
                minDistance as distance,
                avgWeight as activationStrength
             ORDER BY minDistance ASC, avgWeight DESC
             LIMIT %d',
            $maxDepth,
            $maxResults
        );

        $result = $this->neo4j->run($query, [
            'startConcept' => $startConcept,
            'threshold' => $threshold
        ]);

        $activatedConcepts = [];
        foreach ($result as $record) {
            $activatedConcepts[] = [
                'name' => $record->get('name'),
                'type' => $record->get('type'),
                'description' => $record->get('description'),
                'distance' => $record->get('distance'),
                'activationStrength' => round($record->get('activationStrength'), 3)
            ];
        }

        return [
            'startConcept' => $startConcept,
            'parameters' => [
                'threshold' => $threshold,
                'maxDepth' => $maxDepth,
                'maxResults' => $maxResults
            ],
            'activatedConcepts' => $activatedConcepts,
            'totalActivated' => count($activatedConcepts)
        ];
    }

    /**
     * Get node group for visualization
     */
    private function getNodeGroup(string $type, bool $isPrimitive): int
    {
        if ($isPrimitive) {
            return match($type) {
                'image_schema' => 1,
                'csp' => 2,
                'meta_schema' => 3,
                default => 4
            };
        }

        return match($type) {
            'derived' => 5,
            'primitive' => 6,
            default => 7
        };
    }
}