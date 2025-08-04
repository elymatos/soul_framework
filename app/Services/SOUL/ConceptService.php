<?php

namespace App\Services\SOUL;

use App\Data\SOUL\CreateConceptData;
use App\Data\SOUL\UpdateConceptData;
use App\Data\SOUL\CreateRelationshipData;
use App\Data\SOUL\SearchConceptData;
use App\Repositories\Neo4jConceptRepository;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * SOUL Concept Service
 * 
 * Handles business logic for SOUL Framework concept operations,
 * including validation, relationship management, and spreading activation.
 */
class ConceptService
{
    public function __construct(
        private ClientInterface $neo4j,
        private Neo4jConceptRepository $repository
    ) {}

    /**
     * Create a new concept with validation
     */
    public function createConcept(CreateConceptData $data): array
    {
        // Check if concept name already exists
        if ($this->conceptExists($data->name)) {
            throw new \InvalidArgumentException("Concept '{$data->name}' already exists");
        }

        // Validate primitive concept rules
        if ($data->isPrimitive && !$this->isValidPrimitiveType($data->type)) {
            throw new \InvalidArgumentException("Invalid primitive type: {$data->type}");
        }

        // Create concept node with appropriate labels
        $labels = implode(':', $data->getLabels());
        $properties = $data->toNeo4jData();
        
        $query = "CREATE (c:{$labels}) SET c = \$properties RETURN c";
        $result = $this->neo4j->run($query, ['properties' => $properties]);

        return $result->first()->get('c')->toArray();
    }

    /**
     * Update an existing concept
     */
    public function updateConcept(string $conceptName, UpdateConceptData $data): array
    {
        if (!$this->conceptExists($conceptName)) {
            throw new \InvalidArgumentException("Concept '{$conceptName}' not found");
        }

        // Check for name conflicts if name is being changed
        if ($data->name !== null && $data->name !== $conceptName && $this->conceptExists($data->name)) {
            throw new \InvalidArgumentException("Concept name '{$data->name}' already exists");
        }

        $properties = $data->toNeo4jData();
        
        $query = 'MATCH (c:Concept {name: $oldName}) SET c += $properties RETURN c';
        $result = $this->neo4j->run($query, [
            'oldName' => $conceptName,
            'properties' => $properties
        ]);

        return $result->first()->get('c')->toArray();
    }

    /**
     * Delete a concept and its relationships
     */
    public function deleteConcept(string $conceptName): bool
    {
        if (!$this->conceptExists($conceptName)) {
            throw new \InvalidArgumentException("Concept '{$conceptName}' not found");
        }

        // Check if concept has dependents
        $dependents = $this->getConceptDependents($conceptName);
        if (!empty($dependents)) {
            throw new \InvalidArgumentException(
                "Cannot delete concept '{$conceptName}' - has dependent concepts: " . 
                implode(', ', array_column($dependents, 'name'))
            );
        }

        $query = 'MATCH (c:Concept {name: $name}) DETACH DELETE c';
        $result = $this->neo4j->run($query, ['name' => $conceptName]);

        return true;
    }

    /**
     * Create a relationship between concepts
     */
    public function createRelationship(CreateRelationshipData $data): bool
    {
        // Validate both concepts exist
        if (!$this->conceptExists($data->fromConcept)) {
            throw new \InvalidArgumentException("Source concept '{$data->fromConcept}' not found");
        }

        if (!$this->conceptExists($data->toConcept)) {
            throw new \InvalidArgumentException("Target concept '{$data->toConcept}' not found");
        }

        // Check for circular relationships
        if ($this->wouldCreateCircularRelationship($data->fromConcept, $data->toConcept, $data->relationshipType)) {
            throw new \InvalidArgumentException("Relationship would create circular dependency");
        }

        $query = $data->toCypherQuery();
        $parameters = [
            'fromConcept' => $data->fromConcept,
            'toConcept' => $data->toConcept,
            'properties' => $data->toNeo4jData()
        ];

        $result = $this->neo4j->run($query, $parameters);
        return $result->count() > 0;
    }

    /**
     * Search concepts with advanced criteria
     */
    public function searchConcepts(SearchConceptData $data): array
    {
        $query = $data->toCypherQuery();
        $parameters = $data->getQueryParameters();

        $result = $this->neo4j->run($query, $parameters);

        $concepts = [];
        foreach ($result as $record) {
            $concept = $record->get('c')->toArray();
            
            // Add distance for spreading activation results
            if ($data->spreadingActivation && $record->hasKey('distance')) {
                $concept['distance'] = $record->get('distance');
            }
            
            $concepts[] = $concept;
        }

        return $concepts;
    }

    /**
     * Get concept with its relationships
     */
    public function getConceptWithRelationships(string $conceptName): array
    {
        $query = '
            MATCH (c:Concept {name: $name})
            OPTIONAL MATCH (c)-[r]-(related:Concept)
            RETURN c, collect({
                relationship: type(r),
                direction: CASE WHEN startNode(r) = c THEN "outgoing" ELSE "incoming" END,
                related_concept: related.name,
                weight: coalesce(r.weight, 1.0),
                properties: properties(r)
            }) as relationships
        ';

        $result = $this->neo4j->run($query, ['name' => $conceptName]);
        
        if ($result->count() === 0) {
            throw new \InvalidArgumentException("Concept '{$conceptName}' not found");
        }

        $record = $result->first();
        return [
            'concept' => $record->get('c')->toArray(),
            'relationships' => $record->get('relationships')->toArray()
        ];
    }

    /**
     * Initialize SOUL primitives and meta-schemas
     */
    public function initializeSoulPrimitives(): array
    {
        $initialized = [];

        // Image Schema primitives
        $imageSchemaPrimitives = [
            'FORCE', 'REGION', 'OBJECT', 'POINT', 'CURVE', 'AXIS', 'MOVEMENT'
        ];

        foreach ($imageSchemaPrimitives as $primitive) {
            try {
                $data = new CreateConceptData(
                    name: $primitive,
                    type: 'image_schema',
                    description: "Image schema primitive: {$primitive}",
                    category: 'primitive',
                    isPrimitive: true
                );
                $this->createConcept($data);
                $initialized[] = $primitive;
            } catch (\Exception $e) {
                // Primitive already exists, skip
            }
        }

        // CSP primitives
        $cspPrimitives = [
            'EMOTION', 'NUMBER', 'STATE', 'CAUSE', 'SCALE'
        ];

        foreach ($cspPrimitives as $primitive) {
            try {
                $data = new CreateConceptData(
                    name: $primitive,
                    type: 'csp',
                    description: "Common Sense Psychology primitive: {$primitive}",
                    category: 'primitive',
                    isPrimitive: true
                );
                $this->createConcept($data);
                $initialized[] = $primitive;
            } catch (\Exception $e) {
                // Primitive already exists, skip
            }
        }

        // Meta-schemas
        $metaSchemas = [
            'ENTITY', 'STATE', 'PROCESS', 'CHANGE'
        ];

        foreach ($metaSchemas as $metaSchema) {
            try {
                $data = new CreateConceptData(
                    name: $metaSchema,
                    type: 'meta_schema',
                    description: "Meta-schema: {$metaSchema}",
                    category: 'meta_schema',
                    isPrimitive: false
                );
                $this->createConcept($data);
                $initialized[] = $metaSchema;
            } catch (\Exception $e) {
                // Meta-schema already exists, skip
            }
        }

        return $initialized;
    }

    /**
     * Check if concept exists
     */
    private function conceptExists(string $name): bool
    {
        $result = $this->neo4j->run(
            'MATCH (c:Concept {name: $name}) RETURN count(c) as count',
            ['name' => $name]
        );

        return $result->first()->get('count') > 0;
    }

    /**
     * Validate primitive concept type
     */
    private function isValidPrimitiveType(string $type): bool
    {
        return in_array($type, ['image_schema', 'csp', 'meta_schema']);
    }

    /**
     * Get concepts that depend on the given concept
     */
    private function getConceptDependents(string $conceptName): array
    {
        $query = '
            MATCH (c:Concept {name: $name})<-[r:IS_A|PART_OF|SUBTYPE_OF]-(dependent:Concept)
            RETURN dependent.name as name
        ';

        $result = $this->neo4j->run($query, ['name' => $conceptName]);
        
        $dependents = [];
        foreach ($result as $record) {
            $dependents[] = ['name' => $record->get('name')];
        }

        return $dependents;
    }

    /**
     * Check if creating a relationship would cause circular dependency
     */
    private function wouldCreateCircularRelationship(string $from, string $to, string $relationshipType): bool
    {
        // Only check for hierarchical relationships
        if (!in_array($relationshipType, ['IS_A', 'PART_OF', 'SUBTYPE_OF'])) {
            return false;
        }

        $query = sprintf(
            'MATCH path = (start:Concept {name: $to})-[:%s*]->(end:Concept {name: $from})
             RETURN count(path) as paths',
            $relationshipType
        );

        $result = $this->neo4j->run($query, ['from' => $from, 'to' => $to]);
        
        return $result->first()->get('paths') > 0;
    }
}