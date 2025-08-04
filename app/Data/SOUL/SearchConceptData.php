<?php

namespace App\Data\SOUL;

use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class SearchConceptData extends Data
{
    public function __construct(
        public ?string $name = null,
        
        #[In(['primitive', 'derived', 'meta_schema', 'image_schema', 'csp'])]
        public ?string $type = null,
        
        #[In(['concept', 'primitive', 'meta_schema'])]
        public ?string $category = null,
        
        public ?bool $isPrimitive = null,
        
        public ?string $description = null,
        
        // Spreading activation parameters
        #[Between(0.0, 1.0)]
        public float $activationThreshold = 0.5,
        
        #[Min(1)]
        public int $maxDepth = 3,
        
        public bool $spreadingActivation = false,
        
        public ?string $startConcept = null,
        
        // Pagination
        #[Min(1)]
        public int $limit = 50,
        
        #[Min(0)]
        public int $offset = 0,
        
        public string $sortBy = 'name',
        
        #[In(['asc', 'desc'])]
        public string $sortOrder = 'asc',
    ) {}

    /**
     * Build Cypher WHERE clause based on search criteria
     */
    public function buildWhereClause(): array
    {
        $conditions = [];
        $parameters = [];

        if ($this->name !== null) {
            $conditions[] = 'c.name CONTAINS $name';
            $parameters['name'] = $this->name;
        }

        if ($this->type !== null) {
            $conditions[] = 'c.type = $type';
            $parameters['type'] = $this->type;
        }

        if ($this->category !== null) {
            $conditions[] = 'c.category = $category';
            $parameters['category'] = $this->category;
        }

        if ($this->isPrimitive !== null) {
            $conditions[] = 'c.is_primitive = $isPrimitive';
            $parameters['isPrimitive'] = $this->isPrimitive;
        }

        if ($this->description !== null) {
            $conditions[] = 'c.description CONTAINS $description';
            $parameters['description'] = $this->description;
        }

        return [
            'conditions' => $conditions,
            'parameters' => $parameters
        ];
    }

    /**
     * Build complete Cypher query for search
     */
    public function toCypherQuery(): string
    {
        $whereData = $this->buildWhereClause();
        $whereClause = empty($whereData['conditions']) 
            ? '' 
            : 'WHERE ' . implode(' AND ', $whereData['conditions']);

        if ($this->spreadingActivation && $this->startConcept) {
            return sprintf(
                'MATCH path = (start:Concept {name: $startConcept})-[*1..%d]-(c:Concept)
                 %s
                 AND all(r in relationships(path) WHERE coalesce(r.weight, 1.0) >= $activationThreshold)
                 RETURN DISTINCT c, length(path) as distance
                 ORDER BY distance, c.%s %s
                 SKIP $offset LIMIT $limit',
                $this->maxDepth,
                $whereClause,
                $this->sortBy,
                strtoupper($this->sortOrder)
            );
        }

        return sprintf(
            'MATCH (c:Concept)
             %s
             RETURN c
             ORDER BY c.%s %s
             SKIP $offset LIMIT $limit',
            $whereClause,
            $this->sortBy,
            strtoupper($this->sortOrder)
        );
    }

    /**
     * Get query parameters including search criteria and pagination
     */
    public function getQueryParameters(): array
    {
        $whereData = $this->buildWhereClause();
        $parameters = $whereData['parameters'];

        $parameters['offset'] = $this->offset;
        $parameters['limit'] = $this->limit;

        if ($this->spreadingActivation && $this->startConcept) {
            $parameters['startConcept'] = $this->startConcept;
            $parameters['activationThreshold'] = $this->activationThreshold;
        }

        return $parameters;
    }
}