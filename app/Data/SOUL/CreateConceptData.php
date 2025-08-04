<?php

namespace App\Data\SOUL;

use App\Services\AppService;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Data;

class CreateConceptData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public string $name,
        
        #[Required, In(['primitive', 'derived', 'meta_schema', 'image_schema', 'csp'])]
        public string $type,
        
        public ?string $description = null,
        
        #[In(['concept', 'primitive', 'meta_schema'])]
        public string $category = 'concept',
        
        public bool $isPrimitive = false,
        
        public array $properties = [],
        
        public ?int $idUser = null,
        
        public string $_token = '',
    ) {
        $this->idUser = $this->idUser ?? AppService::getCurrentIdUser();
    }

    /**
     * Get concept data for Neo4j creation
     */
    public function toNeo4jData(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'category' => $this->category,
            'is_primitive' => $this->isPrimitive,
            'created_by' => $this->idUser,
            'created_at' => now()->toISOString(),
            'properties' => json_encode($this->properties)
        ];
    }

    /**
     * Determine Neo4j labels based on concept type
     */
    public function getLabels(): array
    {
        $labels = ['Concept'];
        
        if ($this->isPrimitive) {
            $labels[] = 'Primitive';
        }
        
        switch ($this->type) {
            case 'image_schema':
                $labels[] = 'ImageSchema';
                break;
            case 'csp':
                $labels[] = 'CSP';
                break;
            case 'meta_schema':
                $labels[] = 'MetaSchema';
                break;
        }
        
        return $labels;
    }
}