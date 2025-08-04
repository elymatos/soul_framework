<?php

namespace App\Data\SOUL;

use App\Services\AppService;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Data;

class CreateRelationshipData extends Data
{
    public function __construct(
        #[Required]
        public string $fromConcept,
        
        #[Required]
        public string $toConcept,
        
        #[Required, In([
            'IS_A', 'PART_OF', 'RELATED_TO', 'CAUSES', 'ENABLES', 
            'INHIBITS', 'COMPONENT_OF', 'SUBTYPE_OF', 'INSTANCE_OF',
            'SIMILAR_TO', 'OPPOSITE_OF', 'BLENDS_WITH'
        ])]
        public string $relationshipType,
        
        #[Between(0.0, 1.0)]
        public float $weight = 1.0,
        
        public array $properties = [],
        
        public ?string $description = null,
        
        public ?int $idUser = null,
        
        public string $_token = '',
    ) {
        $this->idUser = $this->idUser ?? AppService::getCurrentIdUser();
    }

    /**
     * Get relationship data for Neo4j creation
     */
    public function toNeo4jData(): array
    {
        return [
            'weight' => $this->weight,
            'description' => $this->description,
            'created_by' => $this->idUser,
            'created_at' => now()->toISOString(),
            'properties' => json_encode($this->properties)
        ];
    }

    /**
     * Get Cypher query for creating relationship
     */
    public function toCypherQuery(): string
    {
        return sprintf(
            'MATCH (a:Concept {name: $fromConcept}), (b:Concept {name: $toConcept})
             CREATE (a)-[r:%s]->(b)
             SET r += $properties
             RETURN r',
            $this->relationshipType
        );
    }
}