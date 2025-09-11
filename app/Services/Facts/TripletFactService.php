<?php

namespace App\Services\Facts;

use App\Data\Facts\CreateFactData;
use App\Data\Facts\UpdateFactData;
use App\Data\Facts\SearchFactData;
use App\Data\Facts\BrowseFactData;
use App\Soul\Exceptions\Neo4jException;
use Laudis\Neo4j\Contracts\ClientInterface;
use Illuminate\Support\Str;
use Exception;

class TripletFactService
{
    public function __construct(
        private ClientInterface $neo4j
    ) {}

    /**
     * Create a new fact node with concept relationships
     */
    public function createFact(CreateFactData $data): array
    {
        // Validate the triplet structure
        $validation_errors = $data->validateTriplet();
        if (!empty($validation_errors)) {
            throw new Exception('Triplet validation failed: ' . implode(', ', $validation_errors));
        }

        // Generate unique fact ID
        $fact_id = 'fact_' . Str::uuid();

        try {
            // Start transaction
            $transaction = $this->neo4j->beginTransaction();

            // Ensure all concepts exist
            $all_concepts = $data->getAllConcepts();
            $this->ensureConceptsExist($all_concepts, $transaction);

            // Create the fact node
            $fact_properties = array_merge($data->getFactProperties(), ['id' => $fact_id]);
            $this->createFactNode($fact_id, $fact_properties, $transaction);

            // Create concept relationships
            $relationships = $data->getConceptRelationships();
            $this->createConceptRelationships($fact_id, $relationships, $transaction);

            // Update concept statistics
            $this->updateConceptStatistics($all_concepts, $transaction);

            // Commit transaction
            $transaction->commit();

            return [
                'success' => true,
                'fact_id' => $fact_id,
                'statement' => $fact_properties['statement'],
                'concepts_involved' => count($all_concepts),
                'relationships_created' => count($relationships)
            ];

        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback();
            }
            throw new Neo4jException('Failed to create fact: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Update an existing fact
     */
    public function updateFact(UpdateFactData $data): array
    {
        try {
            // Validate update data
            $validation_errors = $data->validateUpdate();
            if (!empty($validation_errors)) {
                throw new Exception('Update validation failed: ' . implode(', ', $validation_errors));
            }

            $transaction = $this->neo4j->beginTransaction();

            // Check if fact exists
            $existing_fact = $this->getFactById($data->fact_id, $transaction);
            if (!$existing_fact) {
                throw new Exception("Fact with ID {$data->fact_id} not found");
            }

            // Update fact properties
            $update_properties = $data->getUpdateProperties();
            if (!empty($update_properties)) {
                $this->updateFactProperties($data->fact_id, $update_properties, $transaction);
            }

            // Update concept relationships if needed
            if ($data->hasConceptUpdates()) {
                $this->updateConceptRelationships($data->fact_id, $data->getConceptRelationshipUpdates(), $transaction);
                
                // Update concept statistics for affected concepts
                $updated_concepts = $data->getAllUpdatedConcepts();
                if (!empty($updated_concepts)) {
                    $this->ensureConceptsExist($updated_concepts, $transaction);
                    $this->updateConceptStatistics($updated_concepts, $transaction);
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'fact_id' => $data->fact_id,
                'properties_updated' => count($update_properties),
                'relationships_updated' => $data->hasConceptUpdates()
            ];

        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback();
            }
            throw new Neo4jException('Failed to update fact: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Delete a fact and its relationships
     */
    public function deleteFact(string $fact_id): array
    {
        try {
            $transaction = $this->neo4j->beginTransaction();

            // Get fact details before deletion for response
            $fact = $this->getFactById($fact_id, $transaction);
            if (!$fact) {
                throw new Exception("Fact with ID {$fact_id} not found");
            }

            // Get all concepts involved for statistics update
            $involved_concepts = $this->getFactConcepts($fact_id, $transaction);

            // Delete the fact node and all its relationships
            $delete_result = $transaction->run(
                'MATCH (f:FactNode {id: $fact_id})
                 DETACH DELETE f
                 RETURN COUNT(f) as deleted_count',
                ['fact_id' => $fact_id]
            );

            $deleted_count = $delete_result->first()->get('deleted_count');
            
            if ($deleted_count === 0) {
                throw new Exception("Failed to delete fact {$fact_id}");
            }

            // Update concept statistics
            $concept_names = array_column($involved_concepts, 'name');
            $this->updateConceptStatistics($concept_names, $transaction);

            $transaction->commit();

            return [
                'success' => true,
                'fact_id' => $fact_id,
                'statement' => $fact['statement'] ?? '',
                'concepts_affected' => count($concept_names)
            ];

        } catch (Exception $e) {
            if (isset($transaction)) {
                $transaction->rollback();
            }
            throw new Neo4jException('Failed to delete fact: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Search facts based on criteria
     */
    public function searchFacts(SearchFactData $data): array
    {
        try {
            // Build search query
            $search_query = $data->buildSearchQuery();
            $count_query = $data->buildCountQuery();

            // Execute count query for pagination
            $count_result = $this->neo4j->run($count_query['query'], $count_query['parameters']);
            $total = $count_result->first()->get('total');

            // Execute main search query
            $search_result = $this->neo4j->run($search_query['query'], $search_query['parameters']);

            $facts = [];
            foreach ($search_result as $record) {
                $fact_node = $record->get('f');
                $facts[] = $this->formatFactNode($fact_node);
            }

            return [
                'facts' => $facts,
                'total' => $total,
                'pagination' => $search_query['pagination'],
                'has_filters' => $data->hasFilters()
            ];

        } catch (Exception $e) {
            throw new Neo4jException('Failed to search facts: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Browse facts with simpler filtering
     */
    public function browseFacts(BrowseFactData $data): array
    {
        try {
            // Build browse query
            $browse_query = $data->buildBrowseQuery();
            $count_query = $data->buildCountQuery();

            // Execute count query
            $count_result = $this->neo4j->run($count_query['query'], $count_query['parameters']);
            $total = $count_result->first()->get('total');

            // Execute main query
            $browse_result = $this->neo4j->run($browse_query['query'], $browse_query['parameters']);

            $facts = [];
            foreach ($browse_result as $record) {
                $fact_node = $record->get('f');
                $fact_data = $this->formatFactNode($fact_node);

                // Add concept data if included
                if ($data->include_concepts && $record->hasValue('concepts')) {
                    $concepts = $record->get('concepts');
                    $fact_data['concepts'] = $this->formatConceptRelationships($concepts);
                }

                $facts[] = $fact_data;
            }

            $result = [
                'facts' => $facts,
                'total' => $total,
                'pagination' => $data->getPaginationInfo($total),
                'has_filters' => $data->hasFilters(),
                'filter_summary' => $data->getFilterSummary()
            ];

            // Add statistics if requested
            if ($data->include_stats) {
                $stats_query = $data->buildStatsQuery();
                $stats_result = $this->neo4j->run($stats_query['query'], $stats_query['parameters']);
                $result['statistics'] = $this->formatStatsResult($stats_result->first());
            }

            return $result;

        } catch (Exception $e) {
            throw new Neo4jException('Failed to browse facts: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get fact by ID with all relationships
     */
    public function getFactWithRelationships(string $fact_id): ?array
    {
        try {
            $result = $this->neo4j->run(
                'MATCH (f:FactNode {id: $fact_id})
                 OPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)
                 RETURN f, collect({concept: c, role: r.role, sequence: r.sequence, strength: r.strength}) as concepts',
                ['fact_id' => $fact_id]
            );

            if ($result->count() === 0) {
                return null;
            }

            $record = $result->first();
            $fact_data = $this->formatFactNode($record->get('f'));
            $fact_data['concepts'] = $this->formatConceptRelationships($record->get('concepts'));

            return $fact_data;

        } catch (Exception $e) {
            throw new Neo4jException('Failed to get fact: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get fact network for visualization
     */
    public function getFactNetwork(string $fact_id, int $depth = 2): array
    {
        try {
            $query = "
                MATCH (center:FactNode {id: \$fact_id})
                OPTIONAL MATCH path = (center)-[:INVOLVES_CONCEPT*1..{$depth}]-(related)
                WITH center, collect(DISTINCT related) as related_nodes, collect(DISTINCT path) as paths
                RETURN center, related_nodes, paths
            ";

            $result = $this->neo4j->run($query, ['fact_id' => $fact_id]);

            if ($result->count() === 0) {
                return ['nodes' => [], 'links' => []];
            }

            $record = $result->first();
            $center_fact = $record->get('center');
            $related_nodes = $record->get('related_nodes');

            // Build network data
            $nodes = [$this->formatNodeForNetwork($center_fact, 'FactNode', true)];
            $links = [];

            foreach ($related_nodes as $node) {
                $nodes[] = $this->formatNodeForNetwork($node, $node->labels()[0] ?? 'Node', false);
            }

            // Build relationships from paths
            // This would require more complex path analysis
            // For now, return basic structure

            return [
                'nodes' => $nodes,
                'links' => $links,
                'center_fact' => $fact_id
            ];

        } catch (Exception $e) {
            throw new Neo4jException('Failed to get fact network: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Ensure concepts exist in the database
     */
    private function ensureConceptsExist(array $concept_names, $transaction): void
    {
        foreach ($concept_names as $concept_name) {
            $transaction->run(
                'MERGE (c:Concept {name: $name})
                 ON CREATE SET c.created_at = datetime(), c.is_primitive = false, c.fact_frequency = 0
                 RETURN c',
                ['name' => $concept_name]
            );
        }
    }

    /**
     * Create fact node
     */
    private function createFactNode(string $fact_id, array $properties, $transaction): void
    {
        $transaction->run(
            'CREATE (f:FactNode $properties)',
            ['properties' => $properties]
        );
    }

    /**
     * Create concept relationships
     */
    private function createConceptRelationships(string $fact_id, array $relationships, $transaction): void
    {
        foreach ($relationships as $relationship) {
            $transaction->run(
                'MATCH (f:FactNode {id: $fact_id}), (c:Concept {name: $concept_name})
                 CREATE (f)-[r:INVOLVES_CONCEPT {
                     role: $role,
                     sequence: $sequence,
                     required: $required,
                     created_at: datetime(),
                     strength: 1.0
                 }]->(c)',
                [
                    'fact_id' => $fact_id,
                    'concept_name' => $relationship['concept'],
                    'role' => $relationship['role'],
                    'sequence' => $relationship['sequence'],
                    'required' => $relationship['required']
                ]
            );
        }
    }

    /**
     * Update concept statistics
     */
    private function updateConceptStatistics(array $concept_names, $transaction): void
    {
        foreach ($concept_names as $concept_name) {
            $transaction->run(
                'MATCH (c:Concept {name: $name})
                 OPTIONAL MATCH (c)<-[r:INVOLVES_CONCEPT]-(f:FactNode)
                 WITH c, count(f) as fact_count
                 SET c.fact_frequency = fact_count, c.updated_at = datetime()',
                ['name' => $concept_name]
            );
        }
    }

    /**
     * Get fact by ID
     */
    private function getFactById(string $fact_id, $transaction = null): ?array
    {
        $client = $transaction ?? $this->neo4j;
        
        $result = $client->run(
            'MATCH (f:FactNode {id: $fact_id}) RETURN f',
            ['fact_id' => $fact_id]
        );

        if ($result->count() === 0) {
            return null;
        }

        return $this->formatFactNode($result->first()->get('f'));
    }

    /**
     * Get concepts involved in a fact
     */
    private function getFactConcepts(string $fact_id, $transaction): array
    {
        $result = $transaction->run(
            'MATCH (f:FactNode {id: $fact_id})-[:INVOLVES_CONCEPT]->(c:Concept)
             RETURN c.name as name, c.fact_frequency as frequency',
            ['fact_id' => $fact_id]
        );

        $concepts = [];
        foreach ($result as $record) {
            $concepts[] = [
                'name' => $record->get('name'),
                'frequency' => $record->get('frequency')
            ];
        }

        return $concepts;
    }

    /**
     * Update fact properties
     */
    private function updateFactProperties(string $fact_id, array $properties, $transaction): void
    {
        $transaction->run(
            'MATCH (f:FactNode {id: $fact_id})
             SET f += $properties',
            ['fact_id' => $fact_id, 'properties' => $properties]
        );
    }

    /**
     * Update concept relationships
     */
    private function updateConceptRelationships(string $fact_id, array $updates, $transaction): void
    {
        foreach ($updates as $role => $concepts) {
            // Remove existing relationships for this role
            $transaction->run(
                'MATCH (f:FactNode {id: $fact_id})-[r:INVOLVES_CONCEPT {role: $role}]->()
                 DELETE r',
                ['fact_id' => $fact_id, 'role' => $role]
            );

            // Add new relationships
            if (is_array($concepts)) {
                foreach ($concepts as $index => $concept) {
                    $transaction->run(
                        'MATCH (f:FactNode {id: $fact_id}), (c:Concept {name: $concept})
                         CREATE (f)-[:INVOLVES_CONCEPT {
                             role: $role,
                             sequence: $sequence,
                             required: $required,
                             created_at: datetime(),
                             strength: 1.0
                         }]->(c)',
                        [
                            'fact_id' => $fact_id,
                            'concept' => $concept,
                            'role' => $role,
                            'sequence' => $index + 1,
                            'required' => in_array($role, ['subject', 'predicate', 'object'])
                        ]
                    );
                }
            } else {
                // Single concept
                $transaction->run(
                    'MATCH (f:FactNode {id: $fact_id}), (c:Concept {name: $concept})
                     CREATE (f)-[:INVOLVES_CONCEPT {
                         role: $role,
                         sequence: 1,
                         required: $required,
                         created_at: datetime(),
                         strength: 1.0
                     }]->(c)',
                    [
                        'fact_id' => $fact_id,
                        'concept' => $concepts,
                        'role' => $role,
                        'required' => in_array($role, ['subject', 'predicate', 'object'])
                    ]
                );
            }
        }
    }

    /**
     * Format fact node for output
     */
    private function formatFactNode($node): array
    {
        return [
            'id' => $node->getProperty('id'),
            'statement' => $node->getProperty('statement'),
            'confidence' => $node->getProperty('confidence', 1.0),
            'domain' => $node->getProperty('domain'),
            'source' => $node->getProperty('source'),
            'description' => $node->getProperty('description'),
            'tags' => $node->getProperty('tags', []),
            'verified' => $node->getProperty('verified', false),
            'fact_type' => $node->getProperty('fact_type', 'fact'),
            'priority' => $node->getProperty('priority', 'medium'),
            'metadata' => $node->getProperty('metadata', []),
            'created_at' => $node->getProperty('created_at'),
            'updated_at' => $node->getProperty('updated_at'),
            'concept_count' => $node->getProperty('concept_count', 0),
            'has_modifiers' => $node->getProperty('has_modifiers', false),
            'has_temporal' => $node->getProperty('has_temporal', false),
            'has_spatial' => $node->getProperty('has_spatial', false),
            'has_causal' => $node->getProperty('has_causal', false)
        ];
    }

    /**
     * Format concept relationships
     */
    private function formatConceptRelationships($concepts): array
    {
        $formatted = [];
        
        foreach ($concepts as $concept_data) {
            if (isset($concept_data['concept'])) {
                $concept = $concept_data['concept'];
                $formatted[] = [
                    'name' => $concept->getProperty('name'),
                    'role' => $concept_data['role'] ?? null,
                    'sequence' => $concept_data['sequence'] ?? null,
                    'strength' => $concept_data['strength'] ?? 1.0,
                    'is_primitive' => $concept->getProperty('is_primitive', false),
                    'fact_frequency' => $concept->getProperty('fact_frequency', 0)
                ];
            }
        }

        // Sort by sequence
        usort($formatted, fn($a, $b) => ($a['sequence'] ?? 0) <=> ($b['sequence'] ?? 0));

        return $formatted;
    }

    /**
     * Format node for network visualization
     */
    private function formatNodeForNetwork($node, string $type, bool $is_center): array
    {
        $base_data = [
            'id' => $node->getProperty('id') ?? $node->getProperty('name'),
            'label' => $node->getProperty('statement') ?? $node->getProperty('name'),
            'type' => $type,
            'is_center' => $is_center
        ];

        if ($type === 'FactNode') {
            $base_data['confidence'] = $node->getProperty('confidence', 1.0);
            $base_data['domain'] = $node->getProperty('domain');
            $base_data['verified'] = $node->getProperty('verified', false);
        } else {
            $base_data['is_primitive'] = $node->getProperty('is_primitive', false);
            $base_data['fact_frequency'] = $node->getProperty('fact_frequency', 0);
        }

        return $base_data;
    }

    /**
     * Format statistics result
     */
    private function formatStatsResult($record): array
    {
        return [
            'total_facts' => $record->get('total_facts'),
            'avg_confidence' => round($record->get('avg_confidence'), 3),
            'verified_count' => $record->get('verified_count'),
            'with_modifiers_count' => $record->get('with_modifiers_count'),
            'domain_count' => $record->get('domain_count'),
            'fact_types' => $record->get('fact_types'),
            'priorities' => $record->get('priorities')
        ];
    }
}