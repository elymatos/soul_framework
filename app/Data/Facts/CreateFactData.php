<?php

namespace App\Data\Facts;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Data;

class CreateFactData extends Data
{
    public function __construct(
        #[Required, Max(500)]
        public string $statement,
        
        #[Required]
        public string $subject_concept,
        
        #[Required]
        public string $predicate_concept,
        
        #[Required]
        public string $object_concept,
        
        public ?array $modifier_concepts = [],
        
        public ?array $temporal_concepts = [],
        
        public ?array $spatial_concepts = [],
        
        public ?array $causal_concepts = [],
        
        #[Between(0.0, 1.0)]
        public ?float $confidence = 1.0,
        
        #[Max(100)]
        public ?string $domain = null,
        
        #[Max(200)]
        public ?string $source = null,
        
        #[Max(1000)]
        public ?string $description = null,
        
        public ?array $tags = [],
        
        public ?bool $verified = false,
        
        #[In(['fact', 'hypothesis', 'rule', 'constraint'])]
        public ?string $fact_type = 'fact',
        
        #[In(['high', 'medium', 'low'])]
        public ?string $priority = 'medium',
        
        public ?array $metadata = []
    ) {}

    /**
     * Get all concept relationships for this fact
     */
    public function getConceptRelationships(): array
    {
        $relationships = [
            [
                'concept' => $this->subject_concept,
                'role' => 'subject',
                'sequence' => 1,
                'required' => true
            ],
            [
                'concept' => $this->predicate_concept,
                'role' => 'predicate',
                'sequence' => 2,
                'required' => true
            ],
            [
                'concept' => $this->object_concept,
                'role' => 'object',
                'sequence' => 3,
                'required' => true
            ]
        ];

        // Add modifier concepts
        $sequence = 4;
        foreach ($this->modifier_concepts ?? [] as $concept) {
            $relationships[] = [
                'concept' => $concept,
                'role' => 'modifier',
                'sequence' => $sequence++,
                'required' => false
            ];
        }

        // Add temporal concepts
        foreach ($this->temporal_concepts ?? [] as $concept) {
            $relationships[] = [
                'concept' => $concept,
                'role' => 'temporal',
                'sequence' => $sequence++,
                'required' => false
            ];
        }

        // Add spatial concepts
        foreach ($this->spatial_concepts ?? [] as $concept) {
            $relationships[] = [
                'concept' => $concept,
                'role' => 'spatial',
                'sequence' => $sequence++,
                'required' => false
            ];
        }

        // Add causal concepts
        foreach ($this->causal_concepts ?? [] as $concept) {
            $relationships[] = [
                'concept' => $concept,
                'role' => 'causal',
                'sequence' => $sequence++,
                'required' => false
            ];
        }

        return $relationships;
    }

    /**
     * Get all unique concepts involved in this fact
     */
    public function getAllConcepts(): array
    {
        $concepts = [
            $this->subject_concept,
            $this->predicate_concept,
            $this->object_concept
        ];

        if ($this->modifier_concepts) {
            $concepts = array_merge($concepts, $this->modifier_concepts);
        }

        if ($this->temporal_concepts) {
            $concepts = array_merge($concepts, $this->temporal_concepts);
        }

        if ($this->spatial_concepts) {
            $concepts = array_merge($concepts, $this->spatial_concepts);
        }

        if ($this->causal_concepts) {
            $concepts = array_merge($concepts, $this->causal_concepts);
        }

        return array_unique($concepts);
    }

    /**
     * Validate triplet structure
     */
    public function validateTriplet(): array
    {
        $errors = [];

        // Check for required triplet components
        if (empty($this->subject_concept)) {
            $errors[] = 'Subject concept is required';
        }

        if (empty($this->predicate_concept)) {
            $errors[] = 'Predicate concept is required';
        }

        if (empty($this->object_concept)) {
            $errors[] = 'Object concept is required';
        }

        // Check for duplicate concepts in core triplet
        $core_concepts = [
            $this->subject_concept,
            $this->predicate_concept,
            $this->object_concept
        ];

        if (count($core_concepts) !== count(array_unique($core_concepts))) {
            $errors[] = 'Core triplet concepts must be unique';
        }

        // Validate statement format
        if (!empty($this->statement)) {
            $expected_pattern = strtolower(
                $this->subject_concept . ' ' . 
                $this->predicate_concept . ' ' . 
                $this->object_concept
            );
            
            $actual_pattern = strtolower(trim($this->statement));
            
            // Allow some flexibility in statement format
            if (!str_contains($actual_pattern, strtolower($this->subject_concept)) ||
                !str_contains($actual_pattern, strtolower($this->predicate_concept)) ||
                !str_contains($actual_pattern, strtolower($this->object_concept))) {
                $errors[] = 'Statement should contain all triplet concepts';
            }
        }

        return $errors;
    }

    /**
     * Generate a canonical statement if not provided
     */
    public function generateCanonicalStatement(): string
    {
        if (!empty($this->statement)) {
            return $this->statement;
        }

        $statement_parts = [
            $this->subject_concept,
            $this->predicate_concept,
            $this->object_concept
        ];

        // Add modifiers if present
        if (!empty($this->modifier_concepts)) {
            $statement_parts = array_merge($statement_parts, $this->modifier_concepts);
        }

        return implode(' ', $statement_parts);
    }

    /**
     * Get fact properties for Neo4j node creation
     */
    public function getFactProperties(): array
    {
        return [
            'statement' => $this->statement ?: $this->generateCanonicalStatement(),
            'confidence' => $this->confidence ?? 1.0,
            'domain' => $this->domain,
            'source' => $this->source,
            'description' => $this->description,
            'tags' => $this->tags ?? [],
            'verified' => $this->verified ?? false,
            'fact_type' => $this->fact_type ?? 'fact',
            'priority' => $this->priority ?? 'medium',
            'metadata' => $this->metadata ?? [],
            'created_at' => now()->toISOString(),
            'concept_count' => count($this->getAllConcepts()),
            'has_modifiers' => !empty($this->modifier_concepts),
            'has_temporal' => !empty($this->temporal_concepts),
            'has_spatial' => !empty($this->spatial_concepts),
            'has_causal' => !empty($this->causal_concepts)
        ];
    }
}