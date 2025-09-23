<?php

namespace App\Services\Neo4j;

class QueryBuilderService
{
    private string $query = '';

    private array $parameters = [];

    private array $parts = [];

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Reset the query builder
     */
    public function reset(): self
    {
        $this->query = '';
        $this->parameters = [];
        $this->parts = [
            'match' => [],
            'create' => [],
            'merge' => [],
            'where' => [],
            'with' => [],
            'return' => [],
            'set' => [],
            'delete' => [],
            'order' => [],
            'skip' => null,
            'limit' => null,
        ];

        return $this;
    }

    /**
     * Add MATCH clause
     */
    public function match(string $pattern): self
    {
        $this->parts['match'][] = $pattern;

        return $this;
    }

    /**
     * Add CREATE clause
     */
    public function create(string $pattern): self
    {
        $this->parts['create'][] = $pattern;

        return $this;
    }

    /**
     * Add MERGE clause
     */
    public function merge(string $pattern): self
    {
        $this->parts['merge'][] = $pattern;

        return $this;
    }

    /**
     * Add WHERE clause
     */
    public function where(string $condition): self
    {
        $this->parts['where'][] = $condition;

        return $this;
    }

    /**
     * Add WHERE clause with parameter
     */
    public function whereParameter(string $field, string $operator, mixed $value): self
    {
        $paramKey = $this->addParameter($value);
        $this->parts['where'][] = "{$field} {$operator} \${$paramKey}";

        return $this;
    }

    /**
     * Add WITH clause
     */
    public function with(string $expression): self
    {
        $this->parts['with'][] = $expression;

        return $this;
    }

    /**
     * Add RETURN clause
     */
    public function returnClause(string $expression): self
    {
        $this->parts['return'][] = $expression;

        return $this;
    }

    /**
     * Add SET clause
     */
    public function set(string $expression): self
    {
        $this->parts['set'][] = $expression;

        return $this;
    }

    /**
     * Add DELETE clause
     */
    public function delete(string $expression): self
    {
        $this->parts['delete'][] = $expression;

        return $this;
    }

    /**
     * Add ORDER BY clause
     */
    public function orderBy(string $field, string $direction = 'ASC'): self
    {
        $this->parts['order'][] = "{$field} {$direction}";

        return $this;
    }

    /**
     * Add SKIP clause
     */
    public function skip(int $count): self
    {
        $this->parts['skip'] = $count;

        return $this;
    }

    /**
     * Add LIMIT clause
     */
    public function limit(int $count): self
    {
        $this->parts['limit'] = $count;

        return $this;
    }

    /**
     * Add parameter and return key
     */
    public function addParameter(mixed $value): string
    {
        $key = 'param_'.count($this->parameters);
        $this->parameters[$key] = $value;

        return $key;
    }

    /**
     * Get parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Build the final Cypher query
     */
    public function build(): string
    {
        $query = [];

        // CREATE clauses
        if (! empty($this->parts['create'])) {
            $query[] = 'CREATE '.implode(', ', $this->parts['create']);
        }

        // MERGE clauses
        if (! empty($this->parts['merge'])) {
            $query[] = 'MERGE '.implode(', ', $this->parts['merge']);
        }

        // MATCH clauses
        if (! empty($this->parts['match'])) {
            $query[] = 'MATCH '.implode(', ', $this->parts['match']);
        }

        // WHERE clauses
        if (! empty($this->parts['where'])) {
            $query[] = 'WHERE '.implode(' AND ', $this->parts['where']);
        }

        // SET clauses
        if (! empty($this->parts['set'])) {
            $query[] = 'SET '.implode(', ', $this->parts['set']);
        }

        // DELETE clauses
        if (! empty($this->parts['delete'])) {
            $query[] = 'DELETE '.implode(', ', $this->parts['delete']);
        }

        // WITH clauses
        if (! empty($this->parts['with'])) {
            $query[] = 'WITH '.implode(', ', $this->parts['with']);
        }

        // RETURN clauses
        if (! empty($this->parts['return'])) {
            $query[] = 'RETURN '.implode(', ', $this->parts['return']);
        }

        // ORDER BY clauses
        if (! empty($this->parts['order'])) {
            $query[] = 'ORDER BY '.implode(', ', $this->parts['order']);
        }

        // SKIP clause
        if ($this->parts['skip'] !== null) {
            $query[] = 'SKIP '.$this->parts['skip'];
        }

        // LIMIT clause
        if ($this->parts['limit'] !== null) {
            $query[] = 'LIMIT '.$this->parts['limit'];
        }

        return implode(' ', $query);
    }

    /**
     * Convert to string (builds the query)
     */
    public function __toString(): string
    {
        return $this->build();
    }

    /**
     * Helper method to create node pattern
     */
    public static function nodePattern(string $variable, string $label = '', array $properties = []): string
    {
        $pattern = "({$variable}";

        if ($label) {
            $pattern .= ":{$label}";
        }

        if (! empty($properties)) {
            $props = [];
            foreach ($properties as $key => $value) {
                if (is_string($value)) {
                    $props[] = "{$key}: '{$value}'";
                } else {
                    $props[] = "{$key}: {$value}";
                }
            }
            $pattern .= ' {'.implode(', ', $props).'}';
        }

        $pattern .= ')';

        return $pattern;
    }

    /**
     * Helper method to create relationship pattern
     */
    public static function relationshipPattern(string $type = '', string $variable = '', array $properties = []): string
    {
        $pattern = '[';

        if ($variable) {
            $pattern .= $variable;
        }

        if ($type) {
            $pattern .= ":{$type}";
        }

        if (! empty($properties)) {
            $props = [];
            foreach ($properties as $key => $value) {
                if (is_string($value)) {
                    $props[] = "{$key}: '{$value}'";
                } else {
                    $props[] = "{$key}: {$value}";
                }
            }
            $pattern .= ' {'.implode(', ', $props).'}';
        }

        $pattern .= ']';

        return $pattern;
    }

    /**
     * Helper method to escape property names
     */
    public static function escapeProperty(string $property): string
    {
        return '`'.str_replace('`', '``', $property).'`';
    }
}
