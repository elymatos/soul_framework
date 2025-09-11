<?php

namespace App\Data\Facts;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Data;

class BrowseFactData extends Data
{
    public function __construct(
        #[Max(200)]
        public ?string $search = null,
        
        #[Max(100)]
        public ?string $domain = null,
        
        #[In(['fact', 'hypothesis', 'rule', 'constraint', 'all'])]
        public ?string $type = 'all',
        
        #[In(['high', 'medium', 'low', 'all'])]
        public ?string $priority = 'all',
        
        #[In(['verified', 'unverified', 'all'])]
        public ?string $verification = 'all',
        
        #[In(['with_modifiers', 'without_modifiers', 'all'])]
        public ?string $modifiers = 'all',
        
        #[In(['recent', 'oldest', 'confidence_high', 'confidence_low', 'alphabetical'])]
        public ?string $sort = 'recent',
        
        #[Between(1, 100)]
        public ?int $limit = 20,
        
        #[Min(0)]
        public ?int $offset = 0,
        
        public ?array $tags = null,
        
        #[Between(1, 5)]
        public ?int $depth = 2,
        
        public ?bool $include_concepts = true,
        
        public ?bool $include_stats = false,
        
        #[In(['list', 'grid', 'network'])]
        public ?string $view_mode = 'list'
    ) {}

    /**
     * Build filters for concept browsing
     */
    public function getFilters(): array
    {
        $filters = [];

        // Domain filter
        if (!empty($this->domain) && $this->domain !== 'all') {
            $filters['domain'] = $this->domain;
        }

        // Type filter
        if (!empty($this->type) && $this->type !== 'all') {
            $filters['fact_type'] = $this->type;
        }

        // Priority filter
        if (!empty($this->priority) && $this->priority !== 'all') {
            $filters['priority'] = $this->priority;
        }

        // Verification filter
        if (!empty($this->verification) && $this->verification !== 'all') {
            $filters['verified'] = $this->verification === 'verified';
        }

        // Modifiers filter
        if (!empty($this->modifiers) && $this->modifiers !== 'all') {
            $filters['has_modifiers'] = $this->modifiers === 'with_modifiers';
        }

        // Tags filter
        if (!empty($this->tags)) {
            $filters['tags'] = $this->tags;
        }

        return $filters;
    }

    /**
     * Get sort configuration
     */
    public function getSortConfig(): array
    {
        return match($this->sort) {
            'recent' => ['field' => 'created_at', 'direction' => 'DESC'],
            'oldest' => ['field' => 'created_at', 'direction' => 'ASC'],
            'confidence_high' => ['field' => 'confidence', 'direction' => 'DESC'],
            'confidence_low' => ['field' => 'confidence', 'direction' => 'ASC'],
            'alphabetical' => ['field' => 'statement', 'direction' => 'ASC'],
            default => ['field' => 'created_at', 'direction' => 'DESC']
        };
    }

    /**
     * Build Cypher query for browsing facts
     */
    public function buildBrowseQuery(): array
    {
        $conditions = [];
        $parameters = [];

        // Search condition
        if (!empty($this->search)) {
            $conditions[] = "(f.statement CONTAINS \$search OR 
                           EXISTS { (f)-[:INVOLVES_CONCEPT]->(c:Concept) WHERE c.name CONTAINS \$search })";
            $parameters['search'] = $this->search;
        }

        // Apply filters
        $filters = $this->getFilters();
        foreach ($filters as $field => $value) {
            if ($field === 'tags' && is_array($value)) {
                // Handle tags array
                $tag_conditions = [];
                foreach ($value as $index => $tag) {
                    $tag_param = "tag_{$index}";
                    $tag_conditions[] = "\${$tag_param} IN f.tags";
                    $parameters[$tag_param] = $tag;
                }
                if (!empty($tag_conditions)) {
                    $conditions[] = "(" . implode(" OR ", $tag_conditions) . ")";
                }
            } else {
                $conditions[] = "f.{$field} = \${$field}";
                $parameters[$field] = $value;
            }
        }

        // Build WHERE clause
        $where_clause = "";
        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $conditions);
        }

        // Sort configuration
        $sort_config = $this->getSortConfig();
        $order_clause = "ORDER BY f.{$sort_config['field']} {$sort_config['direction']}";

        // Build main query
        $base_query = "MATCH (f:FactNode)";
        
        if ($this->include_concepts) {
            $base_query .= "\nOPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)";
        }

        $return_clause = $this->include_concepts 
            ? "RETURN f, collect({concept: c, role: r.role, sequence: r.sequence}) as concepts"
            : "RETURN f";

        $limit = $this->limit ?? 20;
        $offset = $this->offset ?? 0;

        $query = "{$base_query}\n{$where_clause}\n{$return_clause}\n{$order_clause}\nSKIP {$offset} LIMIT {$limit}";

        return [
            'query' => $query,
            'parameters' => $parameters,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Build count query for pagination
     */
    public function buildCountQuery(): array
    {
        $conditions = [];
        $parameters = [];

        // Search condition
        if (!empty($this->search)) {
            $conditions[] = "(f.statement CONTAINS \$search OR 
                           EXISTS { (f)-[:INVOLVES_CONCEPT]->(c:Concept) WHERE c.name CONTAINS \$search })";
            $parameters['search'] = $this->search;
        }

        // Apply filters
        $filters = $this->getFilters();
        foreach ($filters as $field => $value) {
            if ($field === 'tags' && is_array($value)) {
                $tag_conditions = [];
                foreach ($value as $index => $tag) {
                    $tag_param = "tag_{$index}";
                    $tag_conditions[] = "\${$tag_param} IN f.tags";
                    $parameters[$tag_param] = $tag;
                }
                if (!empty($tag_conditions)) {
                    $conditions[] = "(" . implode(" OR ", $tag_conditions) . ")";
                }
            } else {
                $conditions[] = "f.{$field} = \${$field}";
                $parameters[$field] = $value;
            }
        }

        $where_clause = "";
        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $conditions);
        }

        $query = "MATCH (f:FactNode)\n{$where_clause}\nRETURN COUNT(f) as total";

        return [
            'query' => $query,
            'parameters' => $parameters
        ];
    }

    /**
     * Build statistics query
     */
    public function buildStatsQuery(): array
    {
        $filters = $this->getFilters();
        $conditions = [];
        $parameters = [];

        foreach ($filters as $field => $value) {
            if ($field === 'tags' && is_array($value)) {
                $tag_conditions = [];
                foreach ($value as $index => $tag) {
                    $tag_param = "tag_{$index}";
                    $tag_conditions[] = "\${$tag_param} IN f.tags";
                    $parameters[$tag_param] = $tag;
                }
                if (!empty($tag_conditions)) {
                    $conditions[] = "(" . implode(" OR ", $tag_conditions) . ")";
                }
            } else {
                $conditions[] = "f.{$field} = \${$field}";
                $parameters[$field] = $value;
            }
        }

        $where_clause = "";
        if (!empty($conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $conditions);
        }

        $query = "
            MATCH (f:FactNode)
            {$where_clause}
            RETURN 
                COUNT(f) as total_facts,
                AVG(f.confidence) as avg_confidence,
                COUNT(CASE WHEN f.verified = true THEN 1 END) as verified_count,
                COUNT(CASE WHEN f.has_modifiers = true THEN 1 END) as with_modifiers_count,
                COUNT(DISTINCT f.domain) as domain_count,
                collect(DISTINCT f.fact_type) as fact_types,
                collect(DISTINCT f.priority) as priorities
        ";

        return [
            'query' => $query,
            'parameters' => $parameters
        ];
    }

    /**
     * Get pagination info
     */
    public function getPaginationInfo(int $total): array
    {
        $limit = $this->limit ?? 20;
        $offset = $this->offset ?? 0;
        $current_page = floor($offset / $limit) + 1;
        $total_pages = ceil($total / $limit);

        return [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'per_page' => $limit,
            'total' => $total,
            'offset' => $offset,
            'has_next' => $current_page < $total_pages,
            'has_prev' => $current_page > 1
        ];
    }

    /**
     * Check if any filters are applied
     */
    public function hasFilters(): bool
    {
        return !empty($this->search) ||
               (!empty($this->domain) && $this->domain !== 'all') ||
               (!empty($this->type) && $this->type !== 'all') ||
               (!empty($this->priority) && $this->priority !== 'all') ||
               (!empty($this->verification) && $this->verification !== 'all') ||
               (!empty($this->modifiers) && $this->modifiers !== 'all') ||
               !empty($this->tags);
    }

    /**
     * Get filter summary for display
     */
    public function getFilterSummary(): array
    {
        $summary = [];

        if (!empty($this->search)) {
            $summary['search'] = $this->search;
        }

        if (!empty($this->domain) && $this->domain !== 'all') {
            $summary['domain'] = $this->domain;
        }

        if (!empty($this->type) && $this->type !== 'all') {
            $summary['type'] = $this->type;
        }

        if (!empty($this->priority) && $this->priority !== 'all') {
            $summary['priority'] = $this->priority;
        }

        if (!empty($this->verification) && $this->verification !== 'all') {
            $summary['verification'] = $this->verification;
        }

        if (!empty($this->modifiers) && $this->modifiers !== 'all') {
            $summary['modifiers'] = $this->modifiers;
        }

        if (!empty($this->tags)) {
            $summary['tags'] = implode(', ', $this->tags);
        }

        return $summary;
    }
}