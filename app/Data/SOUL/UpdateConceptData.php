<?php

namespace App\Data\SOUL;

use App\Services\AppService;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Data;

class UpdateConceptData extends Data
{
    public function __construct(
        #[Max(255)]
        public ?string $name = null,
        
        #[In(['primitive', 'derived', 'meta_schema', 'image_schema', 'csp'])]
        public ?string $type = null,
        
        public ?string $description = null,
        
        public ?array $properties = null,
        
        public ?int $idUser = null,
        
        public string $_token = '',
    ) {
        $this->idUser = $this->idUser ?? AppService::getCurrentIdUser();
    }

    /**
     * Get concept data for Neo4j update (only non-null values)
     */
    public function toNeo4jData(): array
    {
        $data = [];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        
        if ($this->type !== null) {
            $data['type'] = $this->type;
        }
        
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        
        if ($this->properties !== null) {
            $data['properties'] = json_encode($this->properties);
        }
        
        $data['updated_by'] = $this->idUser;
        $data['updated_at'] = now()->toISOString();
        
        return $data;
    }
}