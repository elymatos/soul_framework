<?php

namespace App\Data\SOUL;

use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Data;

class BrowseConceptData extends Data
{
    public function __construct(
        public ?string $concept = '',

        #[In(['primitive', 'derived', 'meta_schema', 'image_schema', 'csp'])]
        public ?string $type = null,

        #[In(['concept', 'primitive', 'meta_schema'])]
        public ?string $category = null,

        public ?bool $isPrimitive = null,

        public ?string $description = null,

        // Spreading activation parameters
        public bool $spreadingActivation = false,

        public ?string $startConcept = null,

        #[Between(0.0, 1.0)]
        public float $activationThreshold = 0.5,

        #[Min(1)]
        public int $maxDepth = 2,

        // Pagination and sorting
        #[Min(1)]
        public int $limit = 50,

        #[Min(0)]
        public int $offset = 0,

        public string $sortBy = 'name',

        #[In(['asc', 'desc'])]
        public string $sortOrder = 'asc',

        // UI state
        public string $view = 'cards', // cards or list

        public string $_token = '',
    ) {
        $this->_token = csrf_token();
    }

    /**
     * Check if any search filters are active
     */
    public function hasFilters(): bool
    {
        return ! empty($this->concept) ||
               ! is_null($this->type) ||
               ! is_null($this->category) ||
               ! is_null($this->isPrimitive) ||
               ! empty($this->description) ||
               $this->spreadingActivation;
    }

    /**
     * Get active filter summary for display
     */
    public function getFilterSummary(): array
    {
        $filters = [];

        if (! empty($this->concept)) {
            $filters[] = "Name: '{$this->concept}'";
        }

        if (! is_null($this->type)) {
            $filters[] = "Type: {$this->type}";
        }

        if (! is_null($this->category)) {
            $filters[] = "Category: {$this->category}";
        }

        if (! is_null($this->isPrimitive)) {
            $filters[] = $this->isPrimitive ? 'Primitives only' : 'Non-primitives only';
        }

        if (! empty($this->description)) {
            $filters[] = "Description: '{$this->description}'";
        }

        if ($this->spreadingActivation && ! empty($this->startConcept)) {
            $filters[] = "Spreading activation from: {$this->startConcept}";
        }

        return $filters;
    }

    /**
     * Convert to SearchConceptData for service layer
     */
    public function toSearchConceptData(): SearchConceptData
    {
        return new SearchConceptData(
            name: $this->concept,
            type: $this->type,
            category: $this->category,
            isPrimitive: $this->isPrimitive,
            description: $this->description,
            activationThreshold: $this->activationThreshold,
            maxDepth: $this->maxDepth,
            spreadingActivation: $this->spreadingActivation,
            startConcept: $this->startConcept,
            limit: $this->limit,
            offset: $this->offset,
            sortBy: $this->sortBy,
            sortOrder: $this->sortOrder
        );
    }

    /**
     * Get available concept types for filtering
     */
    public static function getAvailableTypes(): array
    {
        return [
            'primitive' => 'Primitive',
            'derived' => 'Derived',
            'meta_schema' => 'Meta-schema',
            'image_schema' => 'Image Schema',
            'csp' => 'CSP (Common Sense Psychology)',
        ];
    }

    /**
     * Get available categories for filtering
     */
    public static function getAvailableCategories(): array
    {
        return [
            'concept' => 'Concept',
            'primitive' => 'Primitive',
            'meta_schema' => 'Meta-schema',
        ];
    }

    /**
     * Get type display name
     */
    public function getTypeDisplayName(): ?string
    {
        if (! $this->type) {
            return null;
        }

        return match ($this->type) {
            'primitive' => 'Primitive',
            'derived' => 'Derived',
            'meta_schema' => 'Meta-schema',
            'image_schema' => 'Image Schema',
            'csp' => 'CSP',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayName(): ?string
    {
        if (! $this->category) {
            return null;
        }

        return match ($this->category) {
            'concept' => 'Concept',
            'primitive' => 'Primitive',
            'meta_schema' => 'Meta-schema',
            default => ucfirst($this->category)
        };
    }
}
