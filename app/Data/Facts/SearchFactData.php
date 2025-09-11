<?php

namespace App\Data\Facts;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Data;

class SearchFactData extends Data
{
    public function __construct(
        #[Max(200)]
        public ?string $statement_search = null,
        
        public ?string $subject_concept = null,
        
        public ?string $predicate_concept = null,
        
        public ?string $object_concept = null,
        
        public ?array $any_concepts = null,
        
        public ?array $all_concepts = null,
        
        #[Max(100)]
        public ?string $domain = null,
        
        #[Max(200)]
        public ?string $source = null,
        
        public ?array $tags = null,
        
        public ?bool $verified = null,
        
        #[In(['fact', 'hypothesis', 'rule', 'constraint'])]
        public ?string $fact_type = null,
        
        #[In(['high', 'medium', 'low'])]
        public ?string $priority = null,
        
        #[Between(0.0, 1.0)]
        public ?float $min_confidence = null,
        
        #[Between(0.0, 1.0)]
        public ?float $max_confidence = null,
        
        public ?bool $has_modifiers = null,
        
        public ?bool $has_temporal = null,
        
        public ?bool $has_spatial = null,
        
        public ?bool $has_causal = null,
        
        #[In(['created_at', 'updated_at', 'confidence', 'statement', 'domain'])]
        public ?string $sort_by = 'created_at',
        
        #[In(['asc', 'desc'])]
        public ?string $sort_direction = 'desc',
        
        #[Min(1)]
        public ?int $page = 1,
        
        #[Between(1, 100)]
        public ?int $per_page = 20,
        
        public ?string $date_from = null,
        
        public ?string $date_to = null,
        
        #[Between(1, 5)]
        public ?int $network_depth = 2,
        
        public ?bool $include_related = false,
        
        #[Max(200)]
        public ?string $full_text_search = null
    ) {}

    /**
     * Build Cypher WHERE clauses for the search
     */
    public function buildWhereClause(): array
    {
        $conditions = [];
        $parameters = [];

        // Statement search
        if (!empty($this->statement_search)) {
            $conditions[] = "f.statement CONTAINS \$statement_search";
            $parameters['statement_search'] = $this->statement_search;
        }

        // Domain filter
        if (!empty($this->domain)) {
            $conditions[] = "f.domain = \$domain";
            $parameters['domain'] = $this->domain;
        }

        // Source filter
        if (!empty($this->source)) {
            $conditions[] = "f.source = \$source";
            $parameters['source'] = $this->source;
        }

        // Verified filter
        if ($this->verified !== null) {
            $conditions[] = "f.verified = \$verified";
            $parameters['verified'] = $this->verified;
        }

        // Fact type filter
        if (!empty($this->fact_type)) {
            $conditions[] = "f.fact_type = \$fact_type";
            $parameters['fact_type'] = $this->fact_type;
        }

        // Priority filter
        if (!empty($this->priority)) {
            $conditions[] = "f.priority = \$priority";
            $parameters['priority'] = $this->priority;
        }

        // Confidence range
        if ($this->min_confidence !== null) {
            $conditions[] = "f.confidence >= \$min_confidence";
            $parameters['min_confidence'] = $this->min_confidence;
        }

        if ($this->max_confidence !== null) {
            $conditions[] = "f.confidence <= \$max_confidence";
            $parameters['max_confidence'] = $this->max_confidence;
        }

        // Boolean property filters
        if ($this->has_modifiers !== null) {
            $conditions[] = "f.has_modifiers = \$has_modifiers";
            $parameters['has_modifiers'] = $this->has_modifiers;
        }

        if ($this->has_temporal !== null) {
            $conditions[] = "f.has_temporal = \$has_temporal";
            $parameters['has_temporal'] = $this->has_temporal;
        }

        if ($this->has_spatial !== null) {
            $conditions[] = "f.has_spatial = \$has_spatial";
            $parameters['has_spatial'] = $this->has_spatial;
        }

        if ($this->has_causal !== null) {
            $conditions[] = "f.has_causal = \$has_causal";
            $parameters['has_causal'] = $this->has_causal;
        }

        // Date range filters
        if (!empty($this->date_from)) {
            $conditions[] = "f.created_at >= datetime(\$date_from)";
            $parameters['date_from'] = $this->date_from;
        }

        if (!empty($this->date_to)) {
            $conditions[] = "f.created_at <= datetime(\$date_to)";
            $parameters['date_to'] = $this->date_to;
        }

        // Tags filter
        if (!empty($this->tags)) {
            $tag_conditions = [];
            foreach ($this->tags as $index => $tag) {
                $tag_param = "tag_{$index}";
                $tag_conditions[] = "\${$tag_param} IN f.tags";
                $parameters[$tag_param] = $tag;
            }
            if (!empty($tag_conditions)) {
                $conditions[] = "(" . implode(" OR ", $tag_conditions) . ")";
            }
        }

        return [
            'conditions' => $conditions,
            'parameters' => $parameters
        ];
    }

    /**
     * Build concept-based search conditions
     */
    public function buildConceptConditions(): array
    {
        $concept_conditions = [];
        $parameters = [];

        // Specific role-based concept searches
        if (!empty($this->subject_concept)) {
            $concept_conditions[] = "EXISTS { (f)-[:INVOLVES_CONCEPT {role: 'subject'}]->(:Concept {name: \$subject_concept}) }";
            $parameters['subject_concept'] = $this->subject_concept;
        }

        if (!empty($this->predicate_concept)) {
            $concept_conditions[] = "EXISTS { (f)-[:INVOLVES_CONCEPT {role: 'predicate'}]->(:Concept {name: \$predicate_concept}) }";
            $parameters['predicate_concept'] = $this->predicate_concept;
        }

        if (!empty($this->object_concept)) {
            $concept_conditions[] = "EXISTS { (f)-[:INVOLVES_CONCEPT {role: 'object'}]->(:Concept {name: \$object_concept}) }";
            $parameters['object_concept'] = $this->object_concept;
        }

        // Any concepts search (OR condition)
        if (!empty($this->any_concepts)) {
            $any_concept_conditions = [];
            foreach ($this->any_concepts as $index => $concept) {
                $param = "any_concept_{$index}";
                $any_concept_conditions[] = "EXISTS { (f)-[:INVOLVES_CONCEPT]->(:Concept {name: \${$param}}) }";
                $parameters[$param] = $concept;
            }
            if (!empty($any_concept_conditions)) {
                $concept_conditions[] = "(" . implode(" OR ", $any_concept_conditions) . ")";
            }
        }

        // All concepts search (AND condition)
        if (!empty($this->all_concepts)) {
            foreach ($this->all_concepts as $index => $concept) {
                $param = "all_concept_{$index}";
                $concept_conditions[] = "EXISTS { (f)-[:INVOLVES_CONCEPT]->(:Concept {name: \${$param}}) }";
                $parameters[$param] = $concept;
            }
        }

        return [
            'conditions' => $concept_conditions,
            'parameters' => $parameters
        ];
    }

    /**
     * Get ORDER BY clause
     */
    public function getOrderClause(): string
    {
        $sort_field = match($this->sort_by) {
            'created_at' => 'f.created_at',
            'updated_at' => 'f.updated_at',
            'confidence' => 'f.confidence',
            'statement' => 'f.statement',
            'domain' => 'f.domain',
            default => 'f.created_at'
        };

        $direction = strtoupper($this->sort_direction ?? 'DESC');

        return "ORDER BY {$sort_field} {$direction}";
    }

    /**
     * Get pagination parameters
     */
    public function getPaginationParams(): array
    {
        $page = max(1, $this->page ?? 1);
        $per_page = min(100, max(1, $this->per_page ?? 20));
        $skip = ($page - 1) * $per_page;

        return [
            'skip' => $skip,
            'limit' => $per_page,
            'page' => $page,
            'per_page' => $per_page
        ];
    }

    /**
     * Build complete search query
     */
    public function buildSearchQuery(): array
    {
        $base_match = "MATCH (f:FactNode)";
        
        // Add concept relationships if needed
        if ($this->hasConceptSearch()) {
            $base_match .= "\nOPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)";
        }

        $where_clause = $this->buildWhereClause();
        $concept_clause = $this->buildConceptConditions();
        
        $all_conditions = array_merge($where_clause['conditions'], $concept_clause['conditions']);
        $all_parameters = array_merge($where_clause['parameters'], $concept_clause['parameters']);

        $where_string = "";
        if (!empty($all_conditions)) {
            $where_string = "WHERE " . implode(" AND ", $all_conditions);
        }

        $order_clause = $this->getOrderClause();
        $pagination = $this->getPaginationParams();

        $query = "{$base_match}\n{$where_string}\nRETURN DISTINCT f\n{$order_clause}\nSKIP {$pagination['skip']} LIMIT {$pagination['limit']}";

        return [
            'query' => $query,
            'parameters' => $all_parameters,
            'pagination' => $pagination
        ];
    }

    /**
     * Build count query for pagination
     */
    public function buildCountQuery(): array
    {
        $base_match = "MATCH (f:FactNode)";
        
        if ($this->hasConceptSearch()) {
            $base_match .= "\nOPTIONAL MATCH (f)-[r:INVOLVES_CONCEPT]->(c:Concept)";
        }

        $where_clause = $this->buildWhereClause();
        $concept_clause = $this->buildConceptConditions();
        
        $all_conditions = array_merge($where_clause['conditions'], $concept_clause['conditions']);
        $all_parameters = array_merge($where_clause['parameters'], $concept_clause['parameters']);

        $where_string = "";
        if (!empty($all_conditions)) {
            $where_string = "WHERE " . implode(" AND ", $all_conditions);
        }

        $query = "{$base_match}\n{$where_string}\nRETURN COUNT(DISTINCT f) as total";

        return [
            'query' => $query,
            'parameters' => $all_parameters
        ];
    }

    /**
     * Check if search includes concept-based filters
     */
    public function hasConceptSearch(): bool
    {
        return !empty($this->subject_concept) ||
               !empty($this->predicate_concept) ||
               !empty($this->object_concept) ||
               !empty($this->any_concepts) ||
               !empty($this->all_concepts);
    }

    /**
     * Check if search has any filters applied
     */
    public function hasFilters(): bool
    {
        return !empty($this->statement_search) ||
               $this->hasConceptSearch() ||
               !empty($this->domain) ||
               !empty($this->source) ||
               !empty($this->tags) ||
               $this->verified !== null ||
               !empty($this->fact_type) ||
               !empty($this->priority) ||
               $this->min_confidence !== null ||
               $this->max_confidence !== null ||
               $this->has_modifiers !== null ||
               $this->has_temporal !== null ||
               $this->has_spatial !== null ||
               $this->has_causal !== null ||
               !empty($this->date_from) ||
               !empty($this->date_to) ||
               !empty($this->full_text_search);
    }
}