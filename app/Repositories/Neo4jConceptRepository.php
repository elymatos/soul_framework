<?php

namespace App\Repositories;

use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Neo4j Concept Repository for SOUL Framework
 * 
 * This repository demonstrates how to interact with Neo4j for managing
 * SOUL concepts, primitives, and their relationships in the graph database.
 * Includes constraint and index management for optimal performance.
 */
class Neo4jConceptRepository
{
    private ClientInterface $neo4j;

    public function __construct()
    {
        $this->neo4j = app('neo4j');
    }

    /**
     * Create database constraints and indexes for optimal performance
     */
    public function createConstraintsAndIndexes(): array
    {
        $results = [];
        
        try {
            // Create uniqueness constraint for concept names
            $this->neo4j->run('CREATE CONSTRAINT concept_name_unique IF NOT EXISTS FOR (c:Concept) REQUIRE c.name IS UNIQUE');
            $results[] = 'Created uniqueness constraint for Concept.name';

            // Create index for concept types
            $this->neo4j->run('CREATE INDEX concept_type_index IF NOT EXISTS FOR (c:Concept) ON (c.type)');
            $results[] = 'Created index for Concept.type';

            // Create index for primitive concepts
            $this->neo4j->run('CREATE INDEX concept_primitive_index IF NOT EXISTS FOR (c:Concept) ON (c.is_primitive)');
            $results[] = 'Created index for Concept.is_primitive';

            // Create index for concept categories
            $this->neo4j->run('CREATE INDEX concept_category_index IF NOT EXISTS FOR (c:Concept) ON (c.category)');
            $results[] = 'Created index for Concept.category';

            // Create constraint for primitive concept names
            $this->neo4j->run('CREATE CONSTRAINT primitive_name_unique IF NOT EXISTS FOR (p:Primitive) REQUIRE p.name IS UNIQUE');
            $results[] = 'Created uniqueness constraint for Primitive.name';

            // Create constraint for meta-schema names
            $this->neo4j->run('CREATE CONSTRAINT meta_schema_name_unique IF NOT EXISTS FOR (m:MetaSchema) REQUIRE m.name IS UNIQUE');
            $results[] = 'Created uniqueness constraint for MetaSchema.name';

            // Create compound index for efficient searches
            $this->neo4j->run('CREATE INDEX concept_type_category_index IF NOT EXISTS FOR (c:Concept) ON (c.type, c.category)');
            $results[] = 'Created compound index for Concept.type and Concept.category';

        } catch (\Exception $e) {
            $results[] = 'Error creating constraints/indexes: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Drop all constraints and indexes (for maintenance)
     */
    public function dropConstraintsAndIndexes(): array
    {
        $results = [];
        
        try {
            // Get all constraints
            $constraintsResult = $this->neo4j->run('SHOW CONSTRAINTS');
            foreach ($constraintsResult as $record) {
                $constraintName = $record->get('name');
                $this->neo4j->run("DROP CONSTRAINT {$constraintName} IF EXISTS");
                $results[] = "Dropped constraint: {$constraintName}";
            }

            // Get all indexes
            $indexesResult = $this->neo4j->run('SHOW INDEXES');
            foreach ($indexesResult as $record) {
                $indexName = $record->get('name');
                if (!str_contains($indexName, 'constraint')) { // Don't drop constraint-backed indexes
                    $this->neo4j->run("DROP INDEX {$indexName} IF EXISTS");
                    $results[] = "Dropped index: {$indexName}";
                }
            }

        } catch (\Exception $e) {
            $results[] = 'Error dropping constraints/indexes: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Get current constraints and indexes status
     */
    public function getConstraintsAndIndexesStatus(): array
    {
        $status = ['constraints' => [], 'indexes' => []];
        
        try {
            // Get constraints
            $constraintsResult = $this->neo4j->run('SHOW CONSTRAINTS');
            foreach ($constraintsResult as $record) {
                $status['constraints'][] = [
                    'name' => $record->get('name'),
                    'type' => $record->get('type'),
                    'entityType' => $record->get('entityType'),
                    'labelsOrTypes' => $record->get('labelsOrTypes'),
                    'properties' => $record->get('properties'),
                    'ownedIndex' => $record->get('ownedIndex')
                ];
            }

            // Get indexes
            $indexesResult = $this->neo4j->run('SHOW INDEXES');
            foreach ($indexesResult as $record) {
                $status['indexes'][] = [
                    'name' => $record->get('name'),
                    'state' => $record->get('state'),
                    'populationPercent' => $record->get('populationPercent'),
                    'type' => $record->get('type'),
                    'entityType' => $record->get('entityType'),
                    'labelsOrTypes' => $record->get('labelsOrTypes'),
                    'properties' => $record->get('properties')
                ];
            }

        } catch (\Exception $e) {
            $status['error'] = 'Error retrieving constraints/indexes: ' . $e->getMessage();
        }

        return $status;
    }

    /**
     * Create a new concept node in Neo4j
     */
    public function createConcept(string $name, string $type, array $properties = []): array
    {
        $params = array_merge([
            'name' => $name,
            'type' => $type,
            'created_at' => now()->toISOString()
        ], $properties);

        $result = $this->neo4j->run(
            'CREATE (c:Concept {name: $name, type: $type, created_at: $created_at}) RETURN c',
            $params
        );

        return $result->first()->get('c')->toArray();
    }

    /**
     * Create a relationship between two concepts
     */
    public function createRelationship(string $fromConcept, string $toConcept, string $relationshipType, array $properties = []): bool
    {
        $query = sprintf(
            'MATCH (a:Concept {name: $from}), (b:Concept {name: $to})
             CREATE (a)-[r:%s]->(b)
             RETURN r',
            $relationshipType
        );

        $result = $this->neo4j->run($query, [
            'from' => $fromConcept,
            'to' => $toConcept
        ]);

        return $result->count() > 0;
    }

    /**
     * Find concepts by spreading activation
     * This is a simplified example - real spreading activation would be more complex
     */
    public function findConceptsByActivation(string $startConcept, float $threshold = 0.5, int $maxDepth = 3): array
    {
        $query = sprintf(
            'MATCH path = (start:Concept {name: $startConcept})-[*1..%d]-(connected:Concept)
             WHERE all(r in relationships(path) WHERE coalesce(r.weight, 1.0) >= $threshold)
             RETURN DISTINCT connected.name as name, connected.type as type, length(path) as distance
             ORDER BY distance',
            $maxDepth
        );

        $result = $this->neo4j->run($query, [
            'startConcept' => $startConcept,
            'threshold' => $threshold
        ]);

        $concepts = [];
        foreach ($result as $record) {
            $concepts[] = [
                'name' => $record->get('name'),
                'type' => $record->get('type'),
                'distance' => $record->get('distance')
            ];
        }

        return $concepts;
    }

    /**
     * Get concept hierarchy (for structural schemas like CLASS & HIERARCHY)
     */
    public function getConceptHierarchy(string $rootConcept): array
    {
        $result = $this->neo4j->run(
            'MATCH path = (root:Concept {name: $root})<-[:IS_A*]-(child:Concept)
             RETURN child.name as name, child.type as type, length(path) as level
             ORDER BY level, name',
            ['root' => $rootConcept]
        );

        $hierarchy = [];
        foreach ($result as $record) {
            $hierarchy[] = [
                'name' => $record->get('name'),
                'type' => $record->get('type'),
                'level' => $record->get('level')
            ];
        }

        return $hierarchy;
    }

    /**
     * Initialize SOUL primitives in Neo4j
     * Creates the basic Image Schema and CSP primitives
     */
    public function initializeSoulPrimitives(): void
    {
        // Image Schema primitives
        $imageSchemaPrimitives = [
            'FORCE', 'REGION', 'OBJECT', 'POINT', 'CURVE', 'AXIS', 'MOVEMENT'
        ];

        // CSP primitives
        $cspPrimitives = [
            'EMOTION', 'NUMBER', 'STATE', 'CAUSE', 'SCALE'
        ];

        // Meta-schemas
        $metaSchemas = [
            'ENTITY', 'STATE', 'PROCESS', 'CHANGE'
        ];

        // Create Image Schema primitives
        foreach ($imageSchemaPrimitives as $primitive) {
            $this->neo4j->run(
                'MERGE (p:Primitive:ImageSchema {name: $name, type: "image_schema", is_primitive: true})',
                ['name' => $primitive]
            );
        }

        // Create CSP primitives
        foreach ($cspPrimitives as $primitive) {
            $this->neo4j->run(
                'MERGE (p:Primitive:CSP {name: $name, type: "csp", is_primitive: true})',
                ['name' => $primitive]
            );
        }

        // Create Meta-schemas
        foreach ($metaSchemas as $metaSchema) {
            $this->neo4j->run(
                'MERGE (m:MetaSchema {name: $name, type: "meta_schema"})',
                ['name' => $metaSchema]
            );
        }
    }
}