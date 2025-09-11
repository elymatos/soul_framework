<?php

namespace App\Data\Facts;

use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Data;

class UpdateFactData extends Data
{
    public function __construct(
        #[Required]
        public string $fact_id,
        
        #[Max(500)]
        public ?string $statement = null,
        
        public ?string $subject_concept = null,
        
        public ?string $predicate_concept = null,
        
        public ?string $object_concept = null,
        
        public ?array $modifier_concepts = null,
        
        public ?array $temporal_concepts = null,
        
        public ?array $spatial_concepts = null,
        
        public ?array $causal_concepts = null,
        
        #[Between(0.0, 1.0)]
        public ?float $confidence = null,
        
        #[Max(100)]
        public ?string $domain = null,
        
        #[Max(200)]
        public ?string $source = null,
        
        #[Max(1000)]
        public ?string $description = null,
        
        public ?array $tags = null,
        
        public ?bool $verified = null,
        
        #[In(['fact', 'hypothesis', 'rule', 'constraint'])]
        public ?string $fact_type = null,
        
        #[In(['high', 'medium', 'low'])]
        public ?string $priority = null,
        
        public ?array $metadata = null
    ) {}

    /**
     * Get only the non-null properties for updating
     */
    public function getUpdateProperties(): array
    {
        $properties = [];

        if ($this->statement !== null) {
            $properties['statement'] = $this->statement;
        }

        if ($this->confidence !== null) {
            $properties['confidence'] = $this->confidence;
        }

        if ($this->domain !== null) {
            $properties['domain'] = $this->domain;
        }

        if ($this->source !== null) {
            $properties['source'] = $this->source;
        }

        if ($this->description !== null) {
            $properties['description'] = $this->description;
        }

        if ($this->tags !== null) {
            $properties['tags'] = $this->tags;
        }

        if ($this->verified !== null) {
            $properties['verified'] = $this->verified;
        }

        if ($this->fact_type !== null) {
            $properties['fact_type'] = $this->fact_type;
        }

        if ($this->priority !== null) {
            $properties['priority'] = $this->priority;
        }

        if ($this->metadata !== null) {
            $properties['metadata'] = $this->metadata;
        }

        // Always update the modified timestamp
        $properties['updated_at'] = now()->toISOString();

        return $properties;
    }

    /**
     * Get concept relationship updates
     */
    public function getConceptRelationshipUpdates(): array
    {
        $updates = [];

        if ($this->subject_concept !== null) {
            $updates['subject'] = $this->subject_concept;
        }

        if ($this->predicate_concept !== null) {
            $updates['predicate'] = $this->predicate_concept;
        }

        if ($this->object_concept !== null) {
            $updates['object'] = $this->object_concept;
        }

        if ($this->modifier_concepts !== null) {
            $updates['modifier'] = $this->modifier_concepts;
        }

        if ($this->temporal_concepts !== null) {
            $updates['temporal'] = $this->temporal_concepts;
        }

        if ($this->spatial_concepts !== null) {
            $updates['spatial'] = $this->spatial_concepts;
        }

        if ($this->causal_concepts !== null) {
            $updates['causal'] = $this->causal_concepts;
        }

        return $updates;
    }

    /**
     * Check if core triplet is being updated
     */
    public function isCoreTripletupdated(): bool
    {
        return $this->subject_concept !== null ||
               $this->predicate_concept !== null ||
               $this->object_concept !== null;
    }

    /**
     * Check if any concept relationships are being updated
     */
    public function hasConceptUpdates(): bool
    {
        return !empty($this->getConceptRelationshipUpdates());
    }

    /**
     * Validate update data
     */
    public function validateUpdate(): array
    {
        $errors = [];

        // If updating core triplet, ensure no conflicts
        if ($this->isCoreTripletupdated()) {
            $core_concepts = array_filter([
                $this->subject_concept,
                $this->predicate_concept,
                $this->object_concept
            ]);

            if (count($core_concepts) !== count(array_unique($core_concepts))) {
                $errors[] = 'Updated core triplet concepts must be unique';
            }
        }

        // Validate confidence range
        if ($this->confidence !== null && ($this->confidence < 0.0 || $this->confidence > 1.0)) {
            $errors[] = 'Confidence must be between 0.0 and 1.0';
        }

        return $errors;
    }

    /**
     * Get all concepts that will be involved after update
     */
    public function getAllUpdatedConcepts(): array
    {
        $concepts = [];

        // Add core concepts if specified
        if ($this->subject_concept !== null) {
            $concepts[] = $this->subject_concept;
        }

        if ($this->predicate_concept !== null) {
            $concepts[] = $this->predicate_concept;
        }

        if ($this->object_concept !== null) {
            $concepts[] = $this->object_concept;
        }

        // Add modifier concepts
        if ($this->modifier_concepts !== null) {
            $concepts = array_merge($concepts, $this->modifier_concepts);
        }

        // Add temporal concepts
        if ($this->temporal_concepts !== null) {
            $concepts = array_merge($concepts, $this->temporal_concepts);
        }

        // Add spatial concepts
        if ($this->spatial_concepts !== null) {
            $concepts = array_merge($concepts, $this->spatial_concepts);
        }

        // Add causal concepts
        if ($this->causal_concepts !== null) {
            $concepts = array_merge($concepts, $this->causal_concepts);
        }

        return array_unique(array_filter($concepts));
    }
}